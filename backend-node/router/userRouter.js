import express from "express";
import {
  register,
  login,
  dashboard,
  logout,
  verifyToken,
} from "../controller/userController.js";
import {
  getProfile,
  updateProfilePicture,
  updatePassword,
} from "../controller/profileController.js";
import { uploadChatFile } from "../controller/chatUploadController.js"; // NEW
import { upload, chatUpload } from "../config/uploadConfig.js"; // UPDATED

const router = express.Router();

// Existing routes
router.post("/register", register);
router.post("/login", login);
router.get("/dashboard", verifyToken, dashboard);
router.post("/logout", logout);

// Profile routes
router.get("/profile", verifyToken, getProfile);
router.post(
  "/profile/picture",
  verifyToken,
  upload.single("profilePicture"),
  updateProfilePicture
);
router.post("/profile/password", verifyToken, updatePassword);

// NEW: Chat file upload route
router.post("/chat/upload", chatUpload.single("file"), uploadChatFile);

export default router;
