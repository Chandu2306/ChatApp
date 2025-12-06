import mongoose from "mongoose";

const connectDb = async () => {
  try {
    await mongoose.connect(
      "mongodb+srv://chandu:chandu@chandu.ssuujbf.mongodb.net/chatapp?retryWrites=true&w=majority"
    );
    console.log("MongoDB connected");
  } catch (error) {
    console.log("MongoDB connection error:", error);
  }
};

export default connectDb;
