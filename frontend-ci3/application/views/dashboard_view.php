<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Chat Dashboard</title>
  <link rel="stylesheet" href="<?php echo base_url("assets/dashboardStyle.css")?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="header">
        <div class="profile-header">
          <img id="userProfilePic" src=<?php echo base_url("uploads/images/default.png")?> class="profile-pic" alt="Profile">
          <div class="profile-info">
            <h3>Welcome, <span id="currentUser">Guest</span></h3>
            <div class="last-seen" id="lastSeen"></div>
          </div>
        </div>
        
        <div class="header-buttons">
          <button class="new-group-btn" onclick="showUpdateModal()">
            <i class="fas fa-user-cog"></i> Update Profile
          </button>
          <button class="new-group-btn" onclick="showNewGroupModal()">
            <i class="fas fa-users"></i> New Group
          </button>
          <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
        </div>
      </div>
      
      <!-- Search Bar -->
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search users and groups...">
      </div>
      
      <!-- Combined Contacts List -->
      <div class="contacts-section">
        <h4>Users & Groups</h4>
        <div class="contacts-list" id="contactsList">
          <div class="loading">Loading contacts...</div>
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
      <div class="chat-header">
        <div class="chat-title" id="chatTitle">
          <div class="chat-title-left">
            <div class="chat-avatar-container" id="chatAvatarContainer">
              <!-- Avatar will be inserted here -->
            </div>
            <div class="chat-title-text">
              <h3 id="chatWith">Select a chat</h3>
              <span id="onlineStatus" class="online-status"></span>
            </div>
          </div>
        </div>
        <div class="chat-actions" id="chatActions" style="display: none;">
          <button class="view-members-btn" onclick="showRoomMembers()" id="viewMembersBtn" style="display: none;">
            <i class="fas fa-users"></i> View Members
          </button>
          <button class="delete-chat-btn" onclick="deleteCurrentChat()" id="deleteChatBtn">
            <i class="fas fa-trash"></i> <span id="deleteBtnText">Delete Chat</span>
          </button>
        </div>
      </div>
      
      <div class="messages-area" id="messages">
        <div class="welcome-screen">
          <div class="welcome-icon">
            <i class="fas fa-comments"></i>
          </div>
          <h3>Welcome to ChatApp</h3>
          <p>Select a contact or group to start chatting</p>
        </div>
      </div>
      
      <!-- UPDATED: Message Input with File Upload -->
      <div class="message-input-area" id="inputArea" style="display: none;">
        <div class="message-input-wrapper">
          <button class="attach-btn" onclick="document.getElementById('fileInput').click()" title="Attach file">
            <i class="fas fa-paperclip"></i>
          </button>
          <input type="file" id="fileInput" style="display: none;" accept="image/*,.pdf,.doc,.docx,.txt,.xls,.xlsx,.zip,.rar">
          
          <input type="text" id="messageInput" placeholder="Type your message...">
          
          <button onclick="sendMessage()" class="send-btn">
            <i class="fas fa-paper-plane"></i> Send
          </button>
        </div>
        
        <!-- File preview area -->
        <div class="file-preview" id="filePreview">
          <div class="file-preview-content">
            <span id="fileName"></span>
            <button onclick="cancelFileUpload()" class="cancel-file-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="file-progress" id="fileProgress">
            <div class="progress-bar" id="progressBar"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Profile Modal -->
  <div class="modal" id="updateModal">
    <div class="modal-content update-modal-content">
      <h3><i class="fas fa-user-cog"></i> Update Profile</h3>
      
      <!-- Profile Picture Section -->
      <div class="profile-section">
        <img id="currentProfilePic" src="https://www.transparentpng.com/download/user/gray-user-profile-icon-png-fP8Q1P.png" class="profile-avatar" alt="Current Profile">
        
        <form id="profilePictureForm">
          <input type="file" id="profilePictureInput" name="profilePicture" accept="image/*" style="display: none;">
          <button type="button" class="upload-btn" onclick="document.getElementById('profilePictureInput').click()">
            <i class="fas fa-upload"></i> Change Profile Picture
          </button>
        </form>
      </div>
      
      <!-- Update Password Section -->
      <h4><i class="fas fa-key"></i> Change Password</h4>
      <form id="updatePasswordForm">
        <div class="form-group">
          <label>Current Password</label>
          <input type="password" id="oldPassword" name="oldPassword" required>
        </div>
        
        <div class="form-group">
          <label>New Password</label>
          <input type="password" id="newPassword" name="newPassword" required>
        </div>
        
        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required>
        </div>
        
        <div class="password-error" id="passwordError"></div>
      </form>
      
      <div class="modal-buttons">
        <button onclick="closeModal('updateModal')" class="cancel-btn">Cancel</button>
        <button onclick="updatePassword()" class="create-btn">Update Password</button>
      </div>
    </div>
  </div>

  <!-- New Group Modal -->
  <div class="modal" id="newGroupModal">
    <div class="modal-content">
      <h3><i class="fas fa-users"></i> Create New Group</h3>
      <input type="text" id="groupName" placeholder="Enter group name" required>
      
      <h4>Select Members:</h4>
      <div class="users-list" id="usersList">
        <div class="loading">Loading users...</div>
      </div>
      
      <div class="selected-count">
        <span id="selectedCount">0 members selected</span>
      </div>
      
      <div class="modal-buttons">
        <button onclick="closeModal('newGroupModal')" class="cancel-btn">Cancel</button>
        <button onclick="createGroup()" class="create-btn" id="createGroupBtn" disabled>Create Group</button>
      </div>
    </div>
  </div>

  <!-- Room Members Modal -->
  <div class="modal" id="roomMembersModal">
    <div class="modal-content">
      <h3><i class="fas fa-user-friends"></i> <span id="roomMembersTitle">Room Members</span></h3>
      
      <div class="admin-controls" id="adminControls" style="display: none;">
        <button class="add-members-btn" onclick="showAddMembersModal()">
          <i class="fas fa-user-plus"></i> Add Members
        </button>
        <button class="remove-members-btn" onclick="showRemoveMembersModal()">
          <i class="fas fa-user-minus"></i> Remove Members
        </button>
      </div>
      
      <div class="members-list" id="membersList">
        <!-- Members will be loaded here -->
      </div>
      
      <div class="modal-buttons">
        <button onclick="closeModal('roomMembersModal')" class="close-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- Add Members Modal -->
  <div class="modal" id="addMembersModal">
    <div class="modal-content">
      <h3><i class="fas fa-user-plus"></i> Add Members to <span id="currentGroupName"></span></h3>
      
      <div class="available-users-list" id="availableUsersList">
        <div class="loading">Loading available users...</div>
      </div>
      
      <div class="selected-count">
        <span id="selectedAddCount">0 members selected</span>
      </div>
      
      <div class="modal-buttons">
        <button onclick="closeModal('addMembersModal')" class="cancel-btn">Cancel</button>
        <button onclick="addSelectedMembers()" class="add-btn" id="addMembersBtn" disabled>Add Members</button>
      </div>
    </div>
  </div>

  <!-- Remove Members Modal -->
  <div class="modal" id="removeMembersModal">
    <div class="modal-content">
      <h3><i class="fas fa-user-minus"></i> Remove Members from <span id="removeGroupName"></span></h3>
      
      <div class="remove-members-list" id="removeMembersList">
        <div class="loading">Loading current members...</div>
      </div>
      
      <div class="selected-count">
        <span id="selectedRemoveCount">0 members selected</span>
      </div>
      
      <div class="modal-buttons">
        <button onclick="closeModal('removeMembersModal')" class="cancel-btn">Cancel</button>
        <button onclick="removeSelectedMembers()" class="remove-btn" id="removeMembersBtn" disabled>Remove Selected</button>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer"></div>

  <!-- Custom Confirm Dialog -->
  <div class="custom-confirm" id="customConfirm" style="display: none;">
    <div class="confirm-box">
      <div class="confirm-icon" id="confirmIcon">
        <i class="fas fa-question-circle"></i>
      </div>
      <h3 id="confirmTitle">Confirm Action</h3>
      <p id="confirmMessage">Are you sure?</p>
      <div class="confirm-buttons">
        <button onclick="confirmNo()" class="confirm-cancel">Cancel</button>
        <button onclick="confirmYes()" class="confirm-ok" id="confirmActionBtn">Confirm</button>
      </div>
    </div>
  </div>

  <script src="http://localhost:4000/socket.io/socket.io.js"></script>
  <script>
    // Global variables
    const socket = io("http://localhost:4000");
    const myUsername = "<?php echo htmlspecialchars($user ?? ''); ?>";
    const API_BASE = "http://localhost:4000";
    
    let currentChat = {
        id: null,
        type: null,
        name: null,
        members: [],
        creator: null,
        avatarUrl: null
    };
    
    let allContacts = [];
    let selectedUsers = new Set();
    let currentModal = null;
    let typingTimeout;
    let myProfilePicture = "/uploads/images/default.png";
    let currentFile = null;
    let fileUploadInProgress = false;

    // Simple function to get unique avatar for each user
    function getUniqueAvatar(username) {
        if (!username) return `${API_BASE}/uploads/images/default.png`;
        
        // First check if we have user's actual profile picture
        const contact = allContacts.find(c => c.username === username);
        if (contact && contact.profilePicture) {
            return `${API_BASE}${contact.profilePicture}`;
        }
        
        // Fallback to generated avatar
        let hash = 0;
        for (let i = 0; i < username.length; i++) {
            hash += username.charCodeAt(i);
        }
        const avatarId = (hash % 70) + 1;
        return `<?php echo base_url("/uploads/images/default.png");?>`
    }

    // When page loads
    window.onload = function() {
        console.log("Chat App started for:", myUsername);
        document.getElementById('currentUser').textContent = myUsername;
        
        // Load user profile on page load
        loadUserProfile();
        
        socket.emit("join", { username: myUsername });
        
        // Setup file upload listener for profile
        document.getElementById('profilePictureInput').addEventListener('change', uploadProfilePicture);
        
        // Setup file upload listener for chat
        document.getElementById('fileInput').addEventListener('change', handleFileSelect);
        
        // Typing indicator
        document.getElementById('messageInput').addEventListener("input", () => {
            if (!currentChat.id) return;

            socket.emit("typing", { roomId: currentChat.id });

            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                socket.emit("stop_typing", { roomId: currentChat.id });
            }, 1000);
        });
        
        // Group creation
        document.getElementById('groupName').addEventListener('input', updateCreateButton);
        document.getElementById('searchInput').addEventListener('input', searchContacts);
        
        // Enter key to send message
        document.getElementById('messageInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    };

    // ============== PROFILE FUNCTIONS ==============
    async function loadUserProfile() {
        try {
            const token = "<?php echo $this->session->userdata('jwt_token') ?? ''; ?>";
            const response = await fetch(`${API_BASE}/profile`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    myProfilePicture = data.profile.profilePicture;
                    document.getElementById('userProfilePic').src = `${API_BASE}${myProfilePicture}`;
                    document.getElementById('currentProfilePic').src = `${API_BASE}${myProfilePicture}`;
                }
            }
        } catch (error) {
            console.error("Error loading profile:", error);
        }
    }

    async function uploadProfilePicture(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            showToast("error", "Please select an image file");
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) { // 5MB limit
            showToast("error", "Image size should be less than 5MB");
            return;
        }
        
        const formData = new FormData();
        formData.append('profilePicture', file);
        
        try {
            const token = "<?php echo $this->session->userdata('jwt_token') ?? ''; ?>";
            const response = await fetch(`${API_BASE}/profile/picture`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast("success", "Profile picture updated!");
                myProfilePicture = result.profilePicture;
                document.getElementById('userProfilePic').src = `${API_BASE}${myProfilePicture}`;
                document.getElementById('currentProfilePic').src = `${API_BASE}${myProfilePicture}`;
                
                // Clear file input
                document.getElementById('profilePictureInput').value = '';
            } else {
                showToast("error", result.message || "Failed to update picture");
            }
        } catch (error) {
            console.error("Upload error:", error);
            showToast("error", "Failed to upload picture");
        }
    }

    async function updatePassword() {
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const errorElement = document.getElementById('passwordError');
        
        // Reset error
        errorElement.style.display = 'none';
        errorElement.textContent = '';
        
        // Validation
        if (!oldPassword || !newPassword || !confirmPassword) {
            errorElement.textContent = "All fields are required";
            errorElement.style.display = 'block';
            return;
        }
        
        if (newPassword !== confirmPassword) {
            errorElement.textContent = "New passwords do not match";
            errorElement.style.display = 'block';
            return;
        }
        
        if (newPassword.length < 6) {
            errorElement.textContent = "Password must be at least 6 characters";
            errorElement.style.display = 'block';
            return;
        }
        
        try {
            const token = "<?php echo $this->session->userdata('jwt_token') ?? ''; ?>";
            const response = await fetch(`${API_BASE}/profile/password`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    oldPassword: oldPassword,
                    newPassword: newPassword
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast("success", "Password updated successfully!");
                // Clear form
                document.getElementById('oldPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
                errorElement.style.display = 'none';

                // Close modal first
            closeModal('updateModal');
            
            // Wait 2 seconds then redirect to login
            setTimeout(() => {
                window.location.href = '<?php echo site_url('auth/logout'); ?>';
            }, 2000);
            } else {
                errorElement.textContent = result.message || "Failed to update password";
                errorElement.style.display = 'block';
            }
        } catch (error) {
            console.error("Update password error:", error);
            errorElement.textContent = "Server error";
            errorElement.style.display = 'block';
        }
    }

    function showUpdateModal() {
        openModal('updateModal');
    }

    // ============== FILE UPLOAD FUNCTIONS ==============
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            showToast("error", "File size must be less than 10MB");
            return;
        }
        
        // Validate file type
        const validTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/jpg',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];
        
        if (!validTypes.includes(file.type)) {
            showToast("error", "Unsupported file type");
            return;
        }
        
        currentFile = file;
        showFilePreview(file);
    }

    function showFilePreview(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
        document.getElementById('fileProgress').style.display = 'none';
    }

    function cancelFileUpload() {
        currentFile = null;
        document.getElementById('fileInput').value = '';
        document.getElementById('filePreview').style.display = 'none';
        fileUploadInProgress = false;
    }

    async function uploadFile(file) {
        if (!currentChat.id || !file) return null;
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('roomId', currentChat.id);
        formData.append('sender', myUsername);
        
        try {
            const token = "<?php echo $this->session->userdata('jwt_token') ?? ''; ?>";
            
            // Show progress
            document.getElementById('fileProgress').style.display = 'block';
            document.getElementById('progressBar').style.width = '30%';
            
            const response = await fetch(`${API_BASE}/chat/upload`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
            
            document.getElementById('progressBar').style.width = '100%';
            
            const result = await response.json();
            
            if (result.success) {
                return result.file;
            } else {
                showToast("error", result.message || "Failed to upload file");
                return null;
            }
        } catch (error) {
            console.error("Upload error:", error);
            showToast("error", "Failed to upload file");
            return null;
        } finally {
            setTimeout(() => {
                document.getElementById('fileProgress').style.display = 'none';
                document.getElementById('progressBar').style.width = '0%';
            }, 500);
        }
    }

    // ============== CHAT FUNCTIONS ==============
    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message && !currentFile) {
            showToast("warning", "Please enter a message or attach a file");
            return;
        }
        
        if (!currentChat.id) {
            showToast("warning", "Please select a conversation first");
            return;
        }
        
        // If there's a file to upload
        if (currentFile && !fileUploadInProgress) {
            fileUploadInProgress = true;
            showToast("info", "Uploading file...");
            
            const uploadedFile = await uploadFile(currentFile);
            
            if (uploadedFile) {
                // Send file message
                socket.emit('send_message', {
                    roomId: currentChat.id,
                    message: message || `${uploadedFile.icon} ${uploadedFile.originalName}`,
                    file: uploadedFile
                });
                
                // Reset
                cancelFileUpload();
                if (message) input.value = '';
            }
            
            fileUploadInProgress = false;
        } 
        // If it's just a text message
        else if (message && !fileUploadInProgress) {
            socket.emit('send_message', {
                roomId: currentChat.id,
                message: message
            });
            
            input.value = '';
            input.focus();
        }
    }

   function addMessage(sender, text, isMyMessage, time, file = null) {
    const messagesDiv = document.getElementById('messages');
    
    if (messagesDiv.querySelector('.loading') || messagesDiv.querySelector('.no-messages')) {
        messagesDiv.innerHTML = '';
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isMyMessage ? 'my' : 'their'} ${file ? 'file-message' : ''}`;
    
    let messageContent = '';
    
    if (file) {
        // Extract file data - handle both socket and database formats
        const isImage = file.isImage || file.fileType === 'image';
        const filePath = file.filePath || file.url || file.path;
        const fileName = file.originalName || file.name || file.filename || 'File';
        const fileSize = file.formattedSize || formatFileSize(file.fileSize || file.size);
        const fileExtension = getFileExtension(fileName);
        
        if (isImage) {
            // IMAGE: No border, no file name display
            messageContent = `
                <div class="message-sender">${isMyMessage ? 'You' : sender}</div>
                <div class="message-file">
                    <div class="file-image-container">
                        <img src="${API_BASE}${filePath}" alt="" class="file-image" onclick="openImagePreview('${API_BASE}${filePath}')" style="cursor: pointer;">
                        <div class="file-image-info" style="display: none;">
                            <div class="file-name">${fileName}</div>
                            ${fileSize ? `<div class="file-size">${fileSize}</div>` : ''}
                        </div>
                    </div>
                    <div style="margin-top: 8px;">
                        <a href="${API_BASE}${filePath}" download="${fileName}" class="file-download">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
                <div class="message-time">${time || getCurrentTime()}</div>
            `;
        } else {
            // DOCUMENT: Keep border and file info
            messageContent = `
                <div class="message-sender">${isMyMessage ? 'You' : sender}</div>
                <div class="message-file">
                    <div class="file-document">
                        <div class="file-icon">📄</div>
                        <div class="file-info">
                            <div class="file-name">${fileName}</div>
                            <div class="file-size">${fileSize}${fileExtension ? ` • ${fileExtension.toUpperCase()}` : ''}</div>
                            <a href="${API_BASE}${filePath}" download="${fileName}" class="file-download">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <div class="message-time">${time || getCurrentTime()}</div>
            `;
        }
    } else {
        // TEXT MESSAGE
        messageContent = `
            <div class="message-sender">${isMyMessage ? 'You' : sender}</div>
            <div class="message-text">${text}</div>
            <div class="message-time">${time || getCurrentTime()}</div>
        `;
    }
    
    messageDiv.innerHTML = messageContent;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Helper function to get file extension
function getFileExtension(filename) {
    if (!filename) return '';
    return filename.split('.').pop() || '';
}
    function displayMessages(messages) {
    const messagesDiv = document.getElementById('messages');
    messagesDiv.innerHTML = '';
    
    if (messages.length === 0) {
        messagesDiv.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
        return;
    }
    
    messages.forEach(msg => {
        const isMyMessage = msg.sender === myUsername || msg.sender === 'You';
        
        // Handle both old and new file structures
        if (msg.type === 'file' && msg.file) {
            addMessage(msg.sender, msg.message, isMyMessage, msg.time, msg.file);
        } else if (msg.file) { // Also handle if file exists without type
            addMessage(msg.sender, msg.message, isMyMessage, msg.time, msg.file);
        } else {
            addMessage(msg.sender, msg.message, isMyMessage, msg.time);
        }
    });
    
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}
    // Image preview modal
    function openImagePreview(imageUrl) {
        const modal = document.createElement('div');
        modal.className = 'image-preview-overlay';
        
        modal.innerHTML = `
            <img src="${imageUrl}">
            <button class="close-preview-btn">×</button>
        `;
        
        modal.onclick = function(e) {
            if (e.target.tagName === 'BUTTON' || e.target === modal) {
                document.body.removeChild(modal);
            }
        };
        
        document.body.appendChild(modal);
    }

    // ============== SOCKET EVENTS ==============
    socket.on("connect", () => {
        console.log("Connected to chat server");
    });

    socket.on("connected", (data) => {
        if (data.success) {
            loadCombinedData();
        }
    });

    socket.on("combined_data", (data) => {
        if (data.success) {
            console.log("Combined data received:", data);
            
            // Process users with profile pictures
            const usersWithAvatars = data.users.map(user => ({
                ...user,
                isUser: true,
                type: "user",
                name: user.username,
                // Get actual profile picture if available
                avatarUrl: user.profilePicture ? `${API_BASE}${user.profilePicture}` : getUniqueAvatar(user.username)
            }));
            
            // Process groups
            const groupsWithAvatars = data.conversations.map(group => ({
                ...group,
                isUser: false,
                type: "group",
                avatarUrl: null
            }));
            
            allContacts = [...usersWithAvatars, ...groupsWithAvatars];
            updateContactsList();
        }
    });

    socket.on("chat_ready", (data) => {
        if (data.success) {
            const otherUser = data.otherUser;
            const contact = allContacts.find(c => c.username === otherUser);
            
            currentChat = {
                id: data.roomId,
                type: "direct",
                name: otherUser,
                members: [myUsername, otherUser],
                creator: myUsername,
                avatarUrl: contact ? contact.avatarUrl : getUniqueAvatar(otherUser)
            };
            
            updateChatUI();
            loadMessages();
        }
    });

    socket.on("message_sent", (data) => {
        if (currentChat.id === data.roomId) {
            if (data.type === 'file') {
                addMessage("You", data.message, true, data.time, data.file);
            } else {
                addMessage("You", data.message, true, data.time);
            }
        }
        loadCombinedData();
    });

    socket.on("new_message", (data) => {
        if (currentChat.id === data.roomId) {
            if (data.type === 'file') {
                addMessage(data.sender, data.message, false, data.time, data.file);
            } else {
                addMessage(data.sender, data.message, false, data.time);
            }
            socket.emit("mark_read", { roomId: data.roomId });
            return;
        }

        const badge = document.getElementById(`badge-${data.roomId}`);
        if (badge) {
            const count = parseInt(badge.textContent || "0") + 1;
            badge.style.display = "flex";
            badge.textContent = count > 9 ? "9+" : count;
        }
    });

    socket.on("messages_loaded", (data) => {
        if (data.success && currentChat.id === data.roomId) {
            displayMessages(data.messages);
        }
    });

    socket.on("room_created", (data) => {
        showToast("success", data.message);
        loadCombinedData();
        closeModal('newGroupModal');
    });

    socket.on("room_deleted", (data) => {
        showToast("info", data.message);
        if (currentChat.id === data.roomId) {
            resetChatArea();
        }
        loadCombinedData();
    });

    socket.on("room_members_list", (data) => {
        if (data.success) {
            displayRoomMembers(data.members, data.creator);
        }
    });

    socket.on("available_users_list", (data) => {
        if (data.success && currentModal === 'add') {
            displayAvailableUsers(data.users);
        }
    });

    socket.on("current_members_list", (data) => {
        if (data.success && currentModal === 'remove') {
            displayCurrentMembers(data.members);
        }
    });

    socket.on("members_added", (data) => {
        showToast("success", data.message || "Members added successfully");
        closeModal('addMembersModal');
        loadRoomMembers();
        loadCombinedData();
    });

    socket.on("members_removed", (data) => {
        showToast("info", data.message || "Members removed");
        closeModal('removeMembersModal');
        loadRoomMembers();
        loadCombinedData();
    });

    socket.on("error", (data) => {
        console.error("Socket error:", data);
        showToast("error", data.message);
    });

    socket.on("user_online", (data) => {
        updateContactStatus(data.username, true);
    });

    socket.on("user_offline", (data) => {
        updateContactStatus(data.username, false);
    });

    socket.on("typing_indicator", (data) => {
        if (currentChat.id !== data.roomId) return;
        const typingEl = document.getElementById("onlineStatus");
        typingEl.textContent = `${data.sender} is typing...`;
        typingEl.style.color = "#fff";
    });

    socket.on("typing_indicator_stop", (data) => {
        if (currentChat.id !== data.roomId) return;
        restoreOnlineStatus();
    });

    // ============== DATA FUNCTIONS ==============
    function loadCombinedData() {
        socket.emit("get_combined_data", { username: myUsername });
    }

    function updateContactsList() {
        const list = document.getElementById('contactsList');
        list.innerHTML = '';
        
        if (allContacts.length === 0) {
            list.innerHTML = '<div class="no-conversations">No contacts yet</div>';
            return;
        }
        
        allContacts.forEach(contact => {
            const item = document.createElement('div');
            item.className = 'contact-item';
            item.dataset.id = contact.id || contact.username;
            item.dataset.type = contact.type;
            
            let displayName = contact.name || contact.username;
            let avatarUrl = contact.avatarUrl;
            let status = contact.isOnline ? 'online' : 'offline';
            let statusText = contact.isOnline ? 'Online' : 'Offline';
            
            // For users with existing chats, show last message
            // For users without chats, show "Start chatting"
            let lastMessageText = contact.lastMessage;
            if (contact.type === 'user' && !contact.hasExistingChat) {
                lastMessageText = 'Start chatting';
            }
            
            item.innerHTML = `
                <div class="contact-avatar ${status}">
                    ${contact.type === 'user' 
                        ? `<img src="${avatarUrl}" alt="${displayName}" class="avatar-image" />`
                        : `<i class="fas fa-users"></i>`
                    }
                </div>

                <div class="contact-info">
                    <div class="contact-name-row">
                        <span class="contact-name">${displayName}</span>
                        <span class="contact-status ${status}">${statusText}</span>
                    </div>
                    <div class="last-message">${lastMessageText}</div>
                    <div class="last-time">${contact.lastMessageTime || ''}</div>
                </div>

                <!-- unread badge -->
                <div class="unread-badge" id="badge-${contact.id || contact.username}">
                    0
                </div>
            `;

            // ---- UNREAD BADGE LOGIC ----
            if (contact.unreadCount && contact.unreadCount > 0) {
                const badge = item.querySelector(`#badge-${contact.id || contact.username}`);
                badge.style.display = "flex";
                badge.textContent = contact.unreadCount > 9 ? "9+" : contact.unreadCount;
            }
            
            item.onclick = () => {
                if (contact.type === 'user') {
                    startChatWithUser(contact.username);
                } else {
                    selectGroupConversation(contact);
                }
            };
            list.appendChild(item);
        });
    }

    function searchContacts() {
        const term = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('.contact-item');
        
        items.forEach(item => {
            const name = item.querySelector('.contact-name').textContent.toLowerCase();
            item.style.display = name.includes(term) ? 'flex' : 'none';
        });
    }

    function updateContactStatus(username, isOnline) {
        allContacts = allContacts.map(contact => {
            if (contact.username === username || (contact.members && contact.members.includes(username))) {
                return { ...contact, isOnline };
            }
            return contact;
        });
        updateContactsList();
    }

    // ============== CHAT FUNCTIONS ==============
    function startChatWithUser(username) {
        socket.emit("create_or_get_chat", { otherUser: username });
        
        // Clear unread badge instantly (optimistic UI)
        const direct = allContacts.find(c => c.username === username);
        if (direct) {
            const badge = document.getElementById(`badge-${direct.id}`);
            if (badge) badge.style.display = "none";
        }
        
        setTimeout(() => {
            socket.emit("mark_read", { roomId: currentChat.id });
        }, 200);
    }

    function selectGroupConversation(room) {
        currentChat = {
            id: room.id,
            type: room.type,
            name: room.name,
            members: room.members,
            creator: room.createdBy,
            avatarUrl: null
        };
        
        const badge = document.getElementById(`badge-${room.id}`);
        if (badge) badge.style.display = "none";

        setTimeout(() => {
            socket.emit("mark_read", { roomId: currentChat.id });
        }, 200);

        updateChatUI();
        loadMessages();
    }

    function updateChatUI() {
        document.getElementById('chatWith').textContent = currentChat.name;
        document.getElementById('chatActions').style.display = 'flex';
        document.getElementById('inputArea').style.display = 'flex';
        
        const viewBtn = document.getElementById('viewMembersBtn');
        viewBtn.style.display = currentChat.type === 'group' ? 'block' : 'none';
        
        const deleteBtn = document.getElementById('deleteBtnText');
        deleteBtn.textContent = currentChat.type === 'group' ? 'Delete Group' : 'Delete Chat';
        
        // Update chat avatar and online status
        const avatarContainer = document.getElementById('chatAvatarContainer');
        const onlineStatus = document.getElementById('onlineStatus');
        
        if (currentChat.type === 'direct') {
            const otherUser = currentChat.members.find(m => m !== myUsername);
            const contact = allContacts.find(c => c.username === otherUser);
            const avatarUrl = currentChat.avatarUrl || getUniqueAvatar(otherUser);
            
            avatarContainer.innerHTML = `
                <div class="chat-avatar ${contact && contact.isOnline ? 'online' : 'offline'}">
                    <img src="${avatarUrl}" alt="${currentChat.name}" />
                </div>
            `;
            
            if (contact) {
                onlineStatus.textContent = contact.isOnline ? 'Online' : 'Offline';
                onlineStatus.className = `online-status ${contact.isOnline ? 'online' : 'offline'}`;
                onlineStatus.style.display = 'inline';
            }
        } else {
            avatarContainer.innerHTML = `
                <div class="chat-avatar group">
                    <i class="fas fa-users"></i>
                </div>
            `;
            onlineStatus.style.display = 'none';
        }
        
        // Clear messages area
        const messagesDiv = document.getElementById('messages');
        messagesDiv.innerHTML = '<div class="loading">Loading messages...</div>';
    }

    function loadMessages() {
        if (!currentChat.id) return;
        socket.emit('load_messages', { roomId: currentChat.id });
    }

    function deleteCurrentChat() {
        if (!currentChat.id) {
            showToast("warning", "No chat selected");
            return;
        }
        
        const message = currentChat.type === 'group' 
            ? `Delete group "${currentChat.name}"? All messages will be lost.`
            : `Delete conversation with ${currentChat.name}?`;
            
        showConfirm(
            currentChat.type === 'group' ? "Delete Group" : "Delete Chat",
            message,
            "delete-red",
            () => {
                socket.emit('delete_room', { roomId: currentChat.id });
                resetChatArea();
            }
        );
    }

    function resetChatArea() {
        currentChat = { id: null, type: null, name: null, members: [], creator: null, avatarUrl: null };
        document.getElementById('chatWith').textContent = 'Select a chat';
        document.getElementById('chatAvatarContainer').innerHTML = '';
        document.getElementById('chatActions').style.display = 'none';
        document.getElementById('inputArea').style.display = 'none';
        document.getElementById('onlineStatus').style.display = 'none';
        document.getElementById('messages').innerHTML = `
            <div class="welcome-screen">
                <div class="welcome-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Welcome to ChatApp</h3>
                <p>Select a contact or group to start chatting</p>
            </div>
        `;
        cancelFileUpload();
    }

    // ============== GROUP CREATION ==============
    function showNewGroupModal() {
        selectedUsers.clear();
        document.getElementById('groupName').value = '';
        document.getElementById('selectedCount').textContent = '0 members selected';
        document.getElementById('createGroupBtn').disabled = true;
        
        // Load available users for group creation (all users except self)
        const users = allContacts.filter(c => c.type === 'user' && c.username !== myUsername);
        displayUsersForSelection(users);
        
        openModal('newGroupModal');
    }

    function toggleUserSelection(username) {
        if (selectedUsers.has(username)) {
            selectedUsers.delete(username);
        } else {
            selectedUsers.add(username);
        }
        
        // Update button states based on current modal
        if (currentModal === 'create') {
            updateSelectedCount('selectedCount');
            updateCreateButton();
        } else if (currentModal === 'add') {
            updateSelectedCount('selectedAddCount');
            document.getElementById('addMembersBtn').disabled = selectedUsers.size === 0;
        } else if (currentModal === 'remove') {
            updateSelectedCount('selectedRemoveCount');
            document.getElementById('removeMembersBtn').disabled = selectedUsers.size === 0;
        }
        
        // Update UI visual selection
        const items = document.querySelectorAll('.user-select-item');
        items.forEach(item => {
            if (item.dataset.username === username) {
                item.classList.toggle('selected');
                const check = item.querySelector('.user-select-check i');
                check.className = selectedUsers.has(username) ? 'fas fa-check-circle selected' : 'fas fa-circle';
            }
        });
    }

    function updateSelectedCount(elementId) {
        const count = selectedUsers.size;
        if (document.getElementById(elementId)) {
            document.getElementById(elementId).textContent = 
                `${count} member${count !== 1 ? 's' : ''} selected`;
        }
    }

    function updateCreateButton() {
        const groupName = document.getElementById('groupName').value.trim();
        document.getElementById('createGroupBtn').disabled = 
            selectedUsers.size === 0 || !groupName;
    }

    function createGroup() {
        const groupName = document.getElementById('groupName').value.trim();
        
        if (!groupName) {
            showToast("warning", "Please enter group name");
            return;
        }
        
        if (selectedUsers.size === 0) {
            showToast("warning", "Please select at least one member");
            return;
        }
        
        socket.emit('create_group', {
            groupName: groupName,
            members: Array.from(selectedUsers)
        });
    }

    // ============== ROOM MEMBERS FUNCTIONS ==============
    function showRoomMembers() {
        if (!currentChat.id || currentChat.type !== 'group') return;
        
        document.getElementById('roomMembersTitle').textContent = currentChat.name;
        const adminControls = document.getElementById('adminControls');
        adminControls.style.display = currentChat.creator === myUsername ? 'flex' : 'none';
        
        loadRoomMembers();
        openModal('roomMembersModal');
    }

    function loadRoomMembers() {
        if (!currentChat.id) return;
        socket.emit('get_room_members', { roomId: currentChat.id });
    }

    function displayRoomMembers(members, creator) {
        const membersList = document.getElementById('membersList');
        membersList.innerHTML = '';
        
        members.forEach(member => {
            const item = document.createElement('div');
            item.className = 'member-item';
            
            item.innerHTML = `
                <div class="member-avatar ${member.isCreator ? 'admin' : ''}">
                    <i class="fas fa-user"></i>
                </div>
                <div class="member-info">
                    <div class="member-name">
                        ${member.username}
                        ${member.isCreator ? ' <span class="admin-badge">Admin</span>' : ''}
                        <span class="member-status ${member.isOnline ? 'online' : 'offline'}">
                            ${member.isOnline ? 'Online' : 'Offline'}
                        </span>
                    </div>
                    <div class="member-role">
                        ${member.isCreator ? 'Group Creator' : 'Member'}
                    </div>
                </div>
            `;
            
            membersList.appendChild(item);
        });
    }

    function showAddMembersModal() {
        if (!currentChat.id) return;
        document.getElementById('currentGroupName').textContent = currentChat.name;
        currentModal = 'add';
        selectedUsers.clear();
        socket.emit('get_available_users', { 
            roomId: currentChat.id, 
            currentUser: myUsername 
        });
        openModal('addMembersModal');
    }

    function showRemoveMembersModal() {
        if (!currentChat.id) return;
        document.getElementById('removeGroupName').textContent = currentChat.name;
        currentModal = 'remove';
        selectedUsers.clear();
        // Get current members for removal (not available users)
        socket.emit('get_current_members', { 
            roomId: currentChat.id
        });
        openModal('removeMembersModal');
    }

    function displayAvailableUsers(users) {
        const list = document.getElementById('availableUsersList');
        list.innerHTML = '';
        
        if (users.length === 0) {
            list.innerHTML = '<div class="no-users">All users are already in this group</div>';
            return;
        }
        
        displayUsersList(users, list);
    }

    function displayCurrentMembers(members) {
        const list = document.getElementById('removeMembersList');
        list.innerHTML = '';
        
        if (members.length === 0) {
            list.innerHTML = '<div class="no-users">No members to remove</div>';
            return;
        }
        
        // Filter out current user (admin) - can't remove self
        const removableMembers = members.filter(m => m.username !== myUsername);
        displayUsersList(removableMembers, list);
    }

    function displayUsersList(users, container) {
        container.innerHTML = '';
        
        users.forEach(user => {
            const item = document.createElement('div');
            item.className = `user-select-item ${selectedUsers.has(user.username) ? 'selected' : ''}`;
            item.dataset.username = user.username;
            
            item.innerHTML = `
                <div class="user-select-avatar ${user.isOnline ? 'online' : 'offline'}">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-select-info">
                    <div class="user-select-name">${user.username}</div>
                    <div class="user-select-status">${user.isOnline ? 'Online' : 'Offline'}</div>
                </div>
                <div class="user-select-check">
                    <i class="fas ${selectedUsers.has(user.username) ? 'fa-check-circle selected' : 'fa-circle'}"></i>
                </div>
            `;
            
            item.onclick = () => toggleUserSelection(user.username);
            container.appendChild(item);
        });
        
        // Update count and button
        if (currentModal === 'add') {
            updateSelectedCount('selectedAddCount');
            document.getElementById('addMembersBtn').disabled = selectedUsers.size === 0;
        } else if (currentModal === 'remove') {
            updateSelectedCount('selectedRemoveCount');
            document.getElementById('removeMembersBtn').disabled = selectedUsers.size === 0;
        }
    }

    function displayUsersForSelection(users) {
        const list = document.getElementById('usersList');
        displayUsersList(users.map(u => ({
            username: u.username,
            isOnline: u.isOnline
        })), list);
    }

    function addSelectedMembers() {
        if (!currentChat.id || selectedUsers.size === 0) return;
        
        socket.emit('add_members', {
            roomId: currentChat.id,
            members: Array.from(selectedUsers)
        });
    }

    function removeSelectedMembers() {
        if (!currentChat.id || selectedUsers.size === 0) return;
        
        showConfirm(
            "Remove Members",
            `Remove ${selectedUsers.size} member(s) from ${currentChat.name}?`,
            "delete-red",
            () => {
                socket.emit('remove_members', {
                    roomId: currentChat.id,
                    members: Array.from(selectedUsers)
                });
            }
        );
    }

    // ============== MODAL FUNCTIONS ==============
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        selectedUsers.clear();
        currentModal = null;
    }

    // ============== TOAST SYSTEM ==============
    function showToast(type, message) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        toast.innerHTML = `
            <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement === container) {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 200);
            }
        }, 5000);
    }

    // ============== CONFIRM DIALOG ==============
    let confirmCallback = null;
    
    function showConfirm(title, message, type, callback) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmActionBtn').className = `confirm-ok ${type}`;
        confirmCallback = callback;
        document.getElementById('customConfirm').style.display = 'flex';
    }
    
    function confirmYes() {
        if (confirmCallback) confirmCallback();
        document.getElementById('customConfirm').style.display = 'none';
        confirmCallback = null;
    }
    
    function confirmNo() {
        document.getElementById('customConfirm').style.display = 'none';
        confirmCallback = null;
    }

    function restoreOnlineStatus() {
        if (currentChat.type === "direct") {
            const otherUser = currentChat.members.find(u => u !== myUsername);
            const contact = allContacts.find(c => c.username === otherUser);

            if (contact) {
                const el = document.getElementById("onlineStatus");
                el.textContent = contact.isOnline ? "Online" : "Offline";
                el.className = `online-status ${contact.isOnline ? 'online' : 'offline'}`;
            }
        } else {
            document.getElementById("onlineStatus").textContent = "";
        }
    }

    // ============== UTILITY ==============
    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function logout() {
        socket.disconnect();
        window.location.href = '<?php echo site_url('auth/logout'); ?>';
    }
  </script>
</body>
</html>