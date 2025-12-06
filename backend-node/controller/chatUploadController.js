import Message from "../models/messageModel.js";
import Room from "../models/roomModel.js";
import path from "path";

// Upload file for chat
export const uploadChatFile = async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: "No file uploaded",
      });
    }

    const { roomId, sender } = req.body;

    if (!roomId || !sender) {
      return res.status(400).json({
        success: false,
        message: "Room ID and sender are required",
      });
    }

    // Determine file type
    const isImage = req.file.mimetype.startsWith("image/");
    const fileType = isImage ? "image" : "document";

    // Determine file path
    let filePath = "";
    if (isImage) {
      filePath = `/uploads/images/${req.file.filename}`;
    } else {
      filePath = `/uploads/files/${req.file.filename}`;
    }

    // Get file icon based on type
    const getFileIcon = (mimeType, fileName) => {
      if (mimeType.startsWith("image/")) return "🖼️";
      if (mimeType === "application/pdf") return "📄";
      if (mimeType.includes("word") || mimeType.includes("document"))
        return "📝";
      if (mimeType.includes("excel") || mimeType.includes("spreadsheet"))
        return "📊";
      if (mimeType.includes("zip") || mimeType.includes("rar")) return "📦";
      if (mimeType === "text/plain") return "📃";
      return "📎";
    };

    const fileIcon = getFileIcon(req.file.mimetype, req.file.originalname);

    // Format file size
    const formatFileSize = (bytes) => {
      if (bytes < 1024) return bytes + " B";
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
      return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    };

    res.json({
      success: true,
      file: {
        filename: req.file.filename,
        originalName: req.file.originalname,
        fileType: fileType,
        filePath: filePath,
        fileSize: req.file.size,
        formattedSize: formatFileSize(req.file.size),
        mimeType: req.file.mimetype,
        icon: fileIcon,
        isImage: isImage,
      },
    });
  } catch (error) {
    console.error("File upload error:", error);
    res.status(500).json({
      success: false,
      message: "Failed to upload file",
    });
  }
};
