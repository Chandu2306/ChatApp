import User from "../models/userModel.js";
import bcrypt from "bcryptjs";
import fs from "fs";
import path from "path";

// Get user profile
export const getProfile = async (req, res) => {
  try {
    const username = req.user.username;
    const user = await User.findOne({ username }).select("-password");

    if (!user) {
      return res.status(404).json({
        success: false,
        message: "User not found",
      });
    }

    res.json({
      success: true,
      profile: {
        username: user.username,
        profilePicture: user.profilePicture,
        createdAt: user.createdAt,
      },
    });
  } catch (error) {
    console.error("Get profile error:", error);
    res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
};

// Update profile picture
export const updateProfilePicture = async (req, res) => {
  try {
    const username = req.user.username;
    const user = await User.findOne({ username });

    if (!user) {
      return res.status(404).json({
        success: false,
        message: "User not found",
      });
    }

    if (req.file) {
      // Delete old picture if not default
      if (user.profilePicture !== "/uploads/images/default.png") {
        const oldPath = path.join(process.cwd(), user.profilePicture);
        if (fs.existsSync(oldPath)) {
          fs.unlinkSync(oldPath);
        }
      }

      user.profilePicture = `/uploads/images/${req.file.filename}`;
      await user.save();
    }

    res.json({
      success: true,
      message: "Profile picture updated",
      profilePicture: user.profilePicture,
    });
  } catch (error) {
    console.error("Profile picture update error:", error);
    res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
};

// Update password
export const updatePassword = async (req, res) => {
  try {
    const username = req.user.username;
    const { oldPassword, newPassword } = req.body;

    const user = await User.findOne({ username });
    if (!user) {
      return res.status(404).json({
        success: false,
        message: "User not found",
      });
    }

    // Verify old password
    const isPasswordValid = await bcrypt.compare(oldPassword, user.password);
    if (!isPasswordValid) {
      return res.status(400).json({
        success: false,
        message: "Current password is incorrect",
      });
    }

    // Hash new password
    const hashedPassword = await bcrypt.hash(newPassword, 10);
    user.password = hashedPassword;
    await user.save();

    res.json({
      success: true,
      message: "Password updated successfully",
    });
  } catch (error) {
    console.error("Update password error:", error);
    res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
};
