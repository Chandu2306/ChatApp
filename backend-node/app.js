import express from "express";
import router from "./router/userRouter.js";
import connectDb from "./db/connectDb.js";
import bodyParser from "body-parser";
import session from "express-session";
import cors from "cors";
import { createServer } from "http";
import { Server } from "socket.io";
import { initializeSocket, formatTime } from "./socket/socketController.js";

const app = express();
const server = createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

connectDb();

app.use(cors());
app.set("view engine", "ejs");
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(bodyParser.json());
app.use("/uploads", express.static("uploads"));

app.use(
  session({
    secret: "mysecretkey987654",
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 1000 * 60 * 60 * 24 },
  })
);

app.use("/", router);

initializeSocket(io);

app.get("/health", (req, res) => {
  res.json({
    status: "OK",
    activeUsers: Object.keys(global.activeUsers).length,
    timestamp: new Date().toISOString(),
  });
});

app.get("/api/dashboard", (req, res) => {
  try {
    const formattedUsers = {};
    Object.keys(global.activeUsers).forEach((socketId) => {
      const user = global.activeUsers[socketId];
      formattedUsers[socketId] = {
        ...user,
        connectedAt: formatTime(user.connectedAt),
      };
    });

    res.json({
      success: true,
      activeUsers: formattedUsers,
      message: "Dashboard data",
      totalUsers: Object.keys(global.activeUsers).length,
    });
  } catch (error) {
    console.error("Dashboard error:", error);
    res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
});

app.use(express.static("public"));

server.listen(4000, "0.0.0.0", () => {
  console.log("Server + Socket.IO running on port 5556");
});
