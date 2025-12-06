import mongoose from "mongoose";

const messageSchema = new mongoose.Schema(
  {
    roomId: {
      type: mongoose.Schema.Types.ObjectId,
      ref: "Room",
      required: true,
      index: true,
    },
    sender: {
      type: String,
      required: true,
    },
    message: {
      type: String,
      default: "",
    },
    // NEW: File fields
    file: {
      filename: String,
      originalName: String,
      fileType: String, // 'image' or 'document'
      filePath: String, // Relative path like '/uploads/images/...'
      fileSize: Number, // In bytes
    },
    readBy: [
      {
        type: String,
        default: [],
      },
    ],
    // NEW: Message type
    type: {
      type: String,
      enum: ["text", "file"],
      default: "text",
    },
  },
  {
    timestamps: true,
  }
);

// Index for faster queries
messageSchema.index({ roomId: 1, createdAt: 1 });

export default mongoose.model("Message", messageSchema);
