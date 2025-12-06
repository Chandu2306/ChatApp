import mongoose from "mongoose";

const roomSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: true,
    },
    type: {
      type: String,
      enum: ["direct", "group"],
      default: "direct",
    },
    members: [
      {
        type: String, // username
        required: true,
      },
    ],
    createdBy: {
      type: String,
      required: true,
    },
    lastMessage: {
      type: String,
      default: "",
    },
    lastMessageTime: {
      type: Date,
      default: Date.now,
    },
  },
  {
    timestamps: true,
  }
);

// Find or create direct room between two users
roomSchema.statics.findOrCreateDirectRoom = async function (user1, user2) {
  // Sort usernames to ensure unique room for same pair
  const members = [user1, user2].sort();
  const roomName = `direct_${members.join("_")}`;

  let room = await this.findOne({
    type: "direct",
    members: { $all: members, $size: 2 },
  });

  if (!room) {
    room = new this({
      name: roomName,
      type: "direct",
      members: members,
      createdBy: user1,
    });
    await room.save();
  }

  return room;
};

export default mongoose.model("Room", roomSchema);
