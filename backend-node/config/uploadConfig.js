import multer from "multer";
import path from "path";
import fs from "fs";

// Ensure folders exist
const makeDir = (folder) => {
  if (!fs.existsSync(folder)) {
    fs.mkdirSync(folder, { recursive: true });
  }
};

makeDir("./uploads/images");
makeDir("./uploads/files");
makeDir("./uploads/chat"); // NEW: Folder for chat files

const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    // For profile pictures
    if (req.baseUrl && req.baseUrl.includes("/profile")) {
      const isImage = file.mimetype.startsWith("image/");
      const folder = isImage ? "./uploads/images" : "./uploads/files";
      cb(null, folder);
    }
    // For chat files (NEW)
    else if (req.baseUrl && req.baseUrl.includes("/chat")) {
      const isImage = file.mimetype.startsWith("image/");
      const folder = isImage ? "./uploads/images" : "./uploads/files";
      cb(null, folder);
    }
    // Default
    else {
      const isImage = file.mimetype.startsWith("image/");
      const folder = isImage ? "./uploads/images" : "./uploads/files";
      cb(null, folder);
    }
  },
  filename: function (req, file, cb) {
    const uniqueName =
      Date.now() +
      "-" +
      Math.round(Math.random() * 1e9) +
      path.extname(file.originalname);
    cb(null, uniqueName);
  },
});

// File filter for chat uploads (NEW)
const fileFilter = (req, file, cb) => {
  // Allow images
  if (file.mimetype.startsWith("image/")) {
    cb(null, true);
  }
  // Allow common document types
  else if (
    file.mimetype === "application/pdf" ||
    file.mimetype === "application/msword" ||
    file.mimetype ===
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ||
    file.mimetype === "application/vnd.ms-excel" ||
    file.mimetype ===
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ||
    file.mimetype === "text/plain" ||
    file.mimetype === "application/zip" ||
    file.mimetype === "application/x-rar-compressed"
  ) {
    cb(null, true);
  } else {
    cb(new Error("Unsupported file type"), false);
  }
};

// For profile pictures (existing)
export const upload = multer({
  storage: storage,
  limits: {
    fileSize: 1024 * 1024 * 10, // 10 MB limit
  },
});

// NEW: For chat files
export const chatUpload = multer({
  storage: storage,
  fileFilter: fileFilter,
  limits: {
    fileSize: 1024 * 1024 * 10, // 10 MB limit
  },
});
