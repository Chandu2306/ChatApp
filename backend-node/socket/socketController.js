import jwt from "jsonwebtoken";
import User from "../models/userModel.js";
import Room from "../models/roomModel.js";
import Message from "../models/messageModel.js";

const onlineUsers = new Map();

// FIX: Initialize global.activeUsers
if (!global.activeUsers) {
  global.activeUsers = {};
}

export const formatTime = (date) => {
  if (!date) return "Just now";
  const now = new Date();
  const messageDate = new Date(date);
  const diff = now - messageDate;
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return "Just now";
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;
  return messageDate.toLocaleDateString();
};

export const initializeSocket = (io) => {
  io.on("connection", (socket) => {
    console.log(`New connection: ${socket.id}`);

    socket.on("join", async (data) => {
      try {
        console.log("Join attempt with data:", data);
        let username = data.username;

        const user = await User.findOne({ username });
        if (!user) {
          socket.emit("error", { message: "User not found" });
          return;
        }

        onlineUsers.set(username, socket.id);
        socket.username = username;

        // FIX: Add user to global.activeUsers for dashboard
        global.activeUsers[username] = {
          username: username,
          socketId: socket.id,
          connectedAt: new Date(),
          online: true,
        };

        console.log(`${username} joined with socket ${socket.id}`);

        // Update user status
        await User.findOneAndUpdate(
          { username },
          { $set: { online: true, lastSeen: new Date() } }
        );

        socket.emit("connected", {
          success: true,
          message: `Welcome ${username}!`,
          username: username,
        });

        socket.broadcast.emit("user_online", { username });

        loadCombinedData(socket, username);
      } catch (error) {
        console.error("Join error:", error);
        socket.emit("error", {
          message: "Authentication failed: " + error.message,
        });
      }
    });
    socket.on("unread_update", (data) => {
      const badge = document.getElementById(`badge-${data.roomId}`);
      if (!badge) return;

      if (data.unreadCount > 0) {
        badge.style.display = "flex";
        badge.textContent = data.unreadCount > 9 ? "9+" : data.unreadCount;
      } else {
        badge.style.display = "none";
      }
    });

    socket.on("get_combined_data", async (data) => {
      try {
        const currentUser = data.username;

        const allUsers = await User.find({ username: { $ne: currentUser } })
          .select("username online lastSeen profilePicture") // ADDED: profilePicture
          .lean();

        const rooms = await Room.find({ members: currentUser })
          .sort({ lastMessageTime: -1 })
          .lean();

        const directChatPartners = [];
        const directRooms = rooms.filter((room) => room.type === "direct");

        for (const room of directRooms) {
          const otherUser = room.members.find((m) => m !== currentUser);
          if (otherUser) {
            directChatPartners.push({
              username: otherUser,
              roomId: room._id.toString(),
            });
          }
        }

        const usersWithChats = await Promise.all(
          allUsers.map(async (user) => {
            const existingDirect = directChatPartners.find(
              (p) => p.username === user.username
            );
            let roomId = null;
            let lastMessage = null;
            let lastMessageTime = "";

            if (existingDirect) {
              roomId = existingDirect.roomId;

              lastMessage = await Message.findOne({
                roomId: existingDirect.roomId,
              })
                .sort({ createdAt: -1 })
                .lean();

              if (lastMessage) {
                lastMessageTime = formatTime(lastMessage.createdAt);
              }
            }

            return {
              id: roomId,
              username: user.username,
              profilePicture: user.profilePicture, // ADDED
              isOnline: onlineUsers.has(user.username) || user.online,
              lastSeen: formatTime(user.lastSeen),
              type: "user",
              lastMessage: lastMessage
                ? lastMessage.message
                : "No messages yet",
              lastMessageTime: lastMessageTime,
              isDirect: !!existingDirect,
              hasExistingChat: !!existingDirect,
            };
          })
        );

        // Process existing conversations (groups)
        const groupsWithDetails = await Promise.all(
          rooms
            .filter((room) => room.type === "group")
            .map(async (room) => {
              const lastMessage = await Message.findOne({ roomId: room._id })
                .sort({ createdAt: -1 })
                .lean();

              return {
                id: room._id.toString(),
                name: room.name,
                originalName: room.name,
                type: "group",
                members: room.members,
                createdBy: room.createdBy,
                lastMessage: lastMessage
                  ? lastMessage.message
                  : "No messages yet",
                lastMessageTime: lastMessage
                  ? formatTime(lastMessage.createdAt)
                  : "",
                isOnline: false,
              };
            })
        );

        socket.emit("combined_data", {
          success: true,
          users: usersWithChats,
          conversations: groupsWithDetails,
          directChats: directChatPartners.map((p) => p.username),
        });
      } catch (error) {
        console.error("Get combined data error:", error);
        socket.emit("error", { message: "Failed to load data" });
      }
    });

    socket.on("create_or_get_chat", async (data) => {
      try {
        const currentUser = socket.username;
        const otherUser = data.otherUser;

        // Find or create direct room using the static method
        const room = await Room.findOrCreateDirectRoom(currentUser, otherUser);

        // Get last message if any
        const lastMessage = await Message.findOne({ roomId: room._id })
          .sort({ createdAt: -1 })
          .lean();

        // Send room info to requester
        socket.emit("chat_ready", {
          success: true,
          roomId: room._id.toString(),
          otherUser: otherUser,
          roomName: otherUser,
          type: "direct",
          lastMessage: lastMessage ? lastMessage.message : "No messages yet",
          lastMessageTime: lastMessage ? formatTime(lastMessage.createdAt) : "",
        });

        // Load messages immediately
        const messages = await Message.find({ roomId: room._id })
          .sort({ createdAt: 1 })
          .limit(100)
          .lean();

        const formattedMessages = messages.map((msg) => ({
          sender: msg.sender,
          message: msg.message,
          time: formatTime(msg.createdAt),
        }));

        socket.emit("messages_loaded", {
          success: true,
          roomId: room._id.toString(),
          messages: formattedMessages,
        });
      } catch (error) {
        console.error("Create chat error:", error);
        socket.emit("error", { message: "Failed to create chat" });
      }
    });

    // Add this to socketController.js after get_room_members

    socket.on("get_current_members", async (data) => {
      try {
        const room = await Room.findById(data.roomId);
        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        const membersWithStatus = room.members.map((member) => ({
          username: member,
          isOnline: onlineUsers.has(member),
          isCreator: member === room.createdBy,
        }));

        socket.emit("current_members_list", {
          success: true,
          members: membersWithStatus,
        });
      } catch (error) {
        console.error("Get current members error:", error);
        socket.emit("error", { message: "Failed to load members" });
      }
    });
    // ========== SEND MESSAGE ==========
    // ========== SEND MESSAGE ==========
    // ========== SEND MESSAGE ==========
    socket.on("send_message", async (data) => {
      try {
        const currentUser = socket.username;

        // Create message object
        const messageData = {
          roomId: data.roomId,
          sender: currentUser,
          type: "text",
          message: data.message,
        };

        // If it's a file message
        if (data.file) {
          messageData.type = "file";
          messageData.message = data.file.originalName; // Use filename as message
          messageData.file = {
            filename: data.file.filename,
            originalName: data.file.originalName,
            fileType: data.file.fileType,
            filePath: data.file.filePath,
            fileSize: data.file.fileSize,
          };
        }

        // Create message
        const message = new Message(messageData);
        await message.save();

        // Update room's last message
        let lastMessageText = data.message;
        if (data.file) {
          const fileIcon = data.file.fileType === "image" ? "🖼️" : "📎";
          lastMessageText = `${fileIcon} ${data.file.originalName}`;
        }

        await Room.findByIdAndUpdate(data.roomId, {
          lastMessage: lastMessageText,
          lastMessageTime: new Date(),
        });

        // Get room details
        const room = await Room.findById(data.roomId);
        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        // Format time
        const time = formatTime(new Date());

        // Prepare response data
        const responseData = {
          roomId: data.roomId,
          sender: currentUser,
          time: time,
        };

        // Add message or file data
        if (data.file) {
          responseData.type = "file";

          // Send COMPLETE file metadata including all fields from upload
          responseData.file = {
            // Database fields
            filename: data.file.filename,
            originalName: data.file.originalName,
            fileType: data.file.fileType,
            filePath: data.file.filePath,
            fileSize: data.file.fileSize,

            // Additional metadata for frontend display
            formattedSize: data.file.formattedSize,
            mimeType: data.file.mimeType,
            icon: data.file.icon,
            isImage: data.file.isImage,
          };

          responseData.message = `${data.file.icon} ${data.file.originalName}`;
        } else {
          responseData.type = "text";
          responseData.message = data.message;
        }

        // Send to sender
        socket.emit("message_sent", responseData);

        // Send to other room members
        room.members.forEach((member) => {
          if (member !== currentUser) {
            const memberSocketId = onlineUsers.get(member);
            if (memberSocketId) {
              io.to(memberSocketId).emit("new_message", responseData);
            }
          }
        });

        // Update data for all members
        room.members.forEach((member) => {
          const memberSocketId = onlineUsers.get(member);
          if (memberSocketId) {
            loadCombinedData(io.to(memberSocketId), member);
          }
        });
      } catch (error) {
        console.error("Send message error:", error);
        socket.emit("error", { message: "Failed to send message" });
      }
    });
    // ========== LOAD MESSAGES ==========
    // ========== LOAD MESSAGES ==========
    // ========== LOAD MESSAGES ==========
    socket.on("load_messages", async (data) => {
      try {
        const messages = await Message.find({ roomId: data.roomId })
          .sort({ createdAt: 1 })
          .limit(100)
          .lean();

        const formattedMessages = messages.map((msg) => {
          const baseMessage = {
            sender: msg.sender,
            time: formatTime(msg.createdAt),
            type: msg.type || "text",
          };

          if (msg.type === "file" && msg.file) {
            // Get file icon based on fileType
            const icon = msg.file.fileType === "image" ? "🖼️" : "📎";

            // Format file size
            const formatFileSize = (bytes) => {
              if (!bytes) return "";
              if (bytes < 1024) return bytes + " B";
              if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
              return (bytes / (1024 * 1024)).toFixed(1) + " MB";
            };

            // Get file extension
            const getFileExtension = (filename) => {
              if (!filename) return "";
              return filename.split(".").pop() || "";
            };

            // Get file type for display
            const fileType =
              msg.file.fileType === "image"
                ? getFileExtension(msg.file.originalName).toUpperCase()
                : msg.file.fileType?.toUpperCase();

            return {
              ...baseMessage,
              message: `${icon} ${msg.file.originalName}`,
              file: {
                ...msg.file,
                icon: icon,
                formattedSize: formatFileSize(msg.file.fileSize),
                isImage: msg.file.fileType === "image",
                mimeType:
                  msg.file.fileType === "image"
                    ? `image/${getFileExtension(msg.file.originalName)}`
                    : msg.file.fileType,
              },
            };
          } else {
            return {
              ...baseMessage,
              message: msg.message,
            };
          }
        });

        socket.emit("messages_loaded", {
          success: true,
          roomId: data.roomId,
          messages: formattedMessages,
        });
      } catch (error) {
        console.error("Load messages error:", error);
        socket.emit("error", { message: "Failed to load messages" });
      }
    });
    // ========== CREATE GROUP ==========
    socket.on("create_group", async (data) => {
      try {
        const currentUser = socket.username;
        const allMembers = [...data.members, currentUser];

        const room = new Room({
          name: data.groupName,
          type: "group",
          members: allMembers,
          createdBy: currentUser,
        });
        await room.save();

        // Notify all members
        allMembers.forEach((member) => {
          const memberSocketId = onlineUsers.get(member);
          if (memberSocketId) {
            io.to(memberSocketId).emit("room_created", {
              success: true,
              roomId: room._id.toString(),
              message: `You were added to group "${data.groupName}"`,
            });
            loadCombinedData(io.to(memberSocketId), member);
          }
        });

        socket.emit("room_created", {
          success: true,
          roomId: room._id.toString(),
          message: `Group "${data.groupName}" created successfully`,
        });

        loadCombinedData(socket, currentUser);
      } catch (error) {
        console.error("Create group error:", error);
        socket.emit("error", { message: "Failed to create group" });
      }
    });

    // ========== GET ROOM MEMBERS ==========
    socket.on("get_room_members", async (data) => {
      try {
        const room = await Room.findById(data.roomId);
        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        const membersWithStatus = room.members.map((member) => ({
          username: member,
          isOnline: onlineUsers.has(member),
          isCreator: member === room.createdBy,
        }));

        socket.emit("room_members_list", {
          success: true,
          roomId: data.roomId,
          roomName: room.name,
          creator: room.createdBy,
          members: membersWithStatus,
        });
      } catch (error) {
        console.error("Get room members error:", error);
        socket.emit("error", { message: "Failed to load room members" });
      }
    });

    // ========== ADD MEMBERS TO GROUP ==========
    socket.on("add_members", async (data) => {
      try {
        const currentUser = socket.username;
        const room = await Room.findById(data.roomId);

        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        if (room.createdBy !== currentUser) {
          socket.emit("error", { message: "Only group admin can add members" });
          return;
        }

        // Filter out existing members
        const newMembers = data.members.filter(
          (member) => !room.members.includes(member)
        );

        if (newMembers.length === 0) {
          socket.emit("error", {
            message: "All users are already in the group",
          });
          return;
        }

        // Add new members
        room.members.push(...newMembers);
        await room.save();

        // Notify new members
        newMembers.forEach((member) => {
          const memberSocketId = onlineUsers.get(member);
          if (memberSocketId) {
            io.to(memberSocketId).emit("members_added", {
              roomId: data.roomId,
              roomName: room.name,
              message: `You were added to group "${room.name}"`,
            });
            loadCombinedData(io.to(memberSocketId), member);
          }
        });

        // Notify existing members
        room.members.forEach((member) => {
          if (member !== currentUser && !newMembers.includes(member)) {
            const memberSocketId = onlineUsers.get(member);
            if (memberSocketId) {
              io.to(memberSocketId).emit("info", {
                message: `${newMembers.length} new members added`,
              });
            }
          }
        });

        socket.emit("members_added", {
          success: true,
          message: `${newMembers.length} members added`,
        });

        loadCombinedData(socket, currentUser);
      } catch (error) {
        console.error("Add members error:", error);
        socket.emit("error", { message: "Failed to add members" });
      }
    });

    // ========== GET AVAILABLE USERS FOR GROUP ==========
    socket.on("get_available_users", async (data) => {
      try {
        const allUsers = await User.find({
          username: { $ne: data.currentUser },
        });

        const room = await Room.findById(data.roomId);
        const groupMembers = room ? room.members : [];

        const availableUsers = allUsers.filter(
          (user) => !groupMembers.includes(user.username)
        );

        const usersWithStatus = availableUsers.map((user) => ({
          username: user.username,
          isOnline: onlineUsers.has(user.username),
        }));

        socket.emit("available_users_list", {
          success: true,
          users: usersWithStatus,
        });
      } catch (error) {
        console.error("Get available users error:", error);
        socket.emit("error", { message: "Failed to load users" });
      }
    });

    // ========== REMOVE MEMBERS FROM GROUP ==========
    socket.on("remove_members", async (data) => {
      try {
        const currentUser = socket.username;
        const room = await Room.findById(data.roomId);

        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        if (room.createdBy !== currentUser) {
          socket.emit("error", {
            message: "Only group admin can remove members",
          });
          return;
        }

        // Don't allow removing self or creator
        const membersToRemove = data.members.filter(
          (member) => member !== currentUser
        );

        if (membersToRemove.length === 0) {
          socket.emit("error", {
            message: "Cannot remove yourself from group",
          });
          return;
        }

        // Remove members
        room.members = room.members.filter(
          (member) => !membersToRemove.includes(member)
        );
        await room.save();

        // Notify removed members
        membersToRemove.forEach((member) => {
          const memberSocketId = onlineUsers.get(member);
          if (memberSocketId) {
            io.to(memberSocketId).emit("member_removed", {
              roomId: data.roomId,
              roomName: room.name,
              message: `You were removed from group "${room.name}"`,
            });
            loadCombinedData(io.to(memberSocketId), member);
          }
        });

        socket.emit("members_removed", {
          success: true,
          message: `${membersToRemove.length} members removed`,
        });

        loadCombinedData(socket, currentUser);
      } catch (error) {
        console.error("Remove members error:", error);
        socket.emit("error", { message: "Failed to remove members" });
      }
    });

    // ========== DELETE ROOM ==========
    socket.on("delete_room", async (data) => {
      try {
        const currentUser = socket.username;
        const room = await Room.findById(data.roomId);

        if (!room) {
          socket.emit("error", { message: "Room not found" });
          return;
        }

        if (room.type === "group" && room.createdBy !== currentUser) {
          socket.emit("error", {
            message: "Only group admin can delete the group",
          });
          return;
        }

        const allMembers = [...room.members];

        // Delete all messages
        await Message.deleteMany({ roomId: data.roomId });

        // Delete the room
        await Room.findByIdAndDelete(data.roomId);

        // Notify all members
        allMembers.forEach((member) => {
          const memberSocketId = onlineUsers.get(member);
          if (memberSocketId) {
            const message =
              room.type === "direct"
                ? `${currentUser} deleted the chat`
                : `Group "${room.name}" was deleted by admin`;

            io.to(memberSocketId).emit("room_deleted", {
              roomId: data.roomId,
              message: message,
            });
            loadCombinedData(io.to(memberSocketId), member);
          }
        });
      } catch (error) {
        console.error("Delete room error:", error);
        socket.emit("error", { message: "Failed to delete room" });
      }
    });

    // ========== USER DISCONNECTS ==========
    socket.on("disconnect", async () => {
      const username = socket.username;
      if (username) {
        console.log(`${username} disconnected`);
        onlineUsers.delete(username);

        // FIX: Remove from global.activeUsers
        delete global.activeUsers[username];

        await User.findOneAndUpdate(
          { username },
          { $set: { online: false, lastSeen: new Date() } }
        );

        socket.broadcast.emit("user_offline", { username });
      }
    });
    socket.on("typing", (data) => {
      const roomId = data.roomId;
      const sender = socket.username;

      Room.findById(roomId).then((room) => {
        if (!room) return;

        room.members.forEach((member) => {
          if (member !== sender) {
            const sid = onlineUsers.get(member);
            if (sid) {
              io.to(sid).emit("typing_indicator", {
                roomId,
                sender: sender,
              });
            }
          }
        });
      });
    });
    socket.on("stop_typing", (data) => {
      const roomId = data.roomId;
      const sender = socket.username;

      Room.findById(roomId).then((room) => {
        if (!room) return;

        room.members.forEach((member) => {
          if (member !== sender) {
            const sid = onlineUsers.get(member);
            if (sid) {
              io.to(sid).emit("typing_indicator_stop", {
                roomId,
                sender,
              });
            }
          }
        });
      });
    });

    // ========== HELPER FUNCTIONS ==========
    async function loadCombinedData(socket, username) {
      try {
        socket.emit("get_combined_data", { username: username });
      } catch (error) {
        console.error("Load combined data error:", error);
      }
    }
  });
};
