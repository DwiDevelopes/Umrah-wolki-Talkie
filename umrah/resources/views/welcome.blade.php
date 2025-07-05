<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transmitter Umrah</title>
    <link rel="icon" type="image/x-icon" href="icon/Hoki Tolki.ico">
    <script src="https://unpkg.com/peerjs@1.3.1/dist/peerjs.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel = "stylesheet" href = "css/css.css">
</head>
<body class="min-h-screen">
    <!-- Loading Screen -->
    <div id="loading-screen" class="fixed inset-0 bg-slate-900 flex flex-col items-center justify-center z-50 transition-opacity duration-500">
        <div class="animate-spin rounded-full h-20 w-20 border-t-2 border-b-2 border-blue-500 mb-4"></div>
        <h2 class="text-xl text-blue-400">Initializing Transmitter Umrah...</h2>
        <p class="text-slate-400 mt-2">Loading audio components and network connections</p>
    </div>

    <!-- Main App -->
    <div class="container mx-auto px-4 py-8 hidden" id="app-container">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                <img src="icon/icon.png" alt="Logo" class="w-10 h-10 mr-3">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-blue-600">Transmitter Umrah</h1>
            </div>
            <div class="flex items-center space-x-4">
                <button id="info-btn" class="p-2 rounded-full bg-slate-200 hover:bg-slate-300 transition">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </header>

        <!-- Connection Status -->
        <div id="connection-status" class="mb-6 hidden">
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-red-500 mr-2 pulse" id="status-indicator"></div>
                <span id="status-text">Disconnected</span>
            </div>
        </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Panel - Room Info -->
                    <div class="card p-4 md:p-6">
                        <h2 class="text-lg md:text-xl font-semibold mb-4">Room Information</h2>
                        
                        <div id="entry-modal">
                            <div class="mb-4">
                                <label for="room-input" class="block text-sm font-medium mb-2">Room ID</label>
                                <input id="room-input" class="w-full px-3 py-2 md:px-4 md:py-2 bg-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Room ID" type="text">
                            </div>
                            <div class="flex flex-col sm:flex-row sm:space-x-3 space-y-2 sm:space-y-0">
                                <button onclick="createRoom()" class="btn-primary py-2 rounded-md flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i> Create Room
                                </button>
                                <button onclick="joinRoom()" class="btn-primary py-2 rounded-md flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Join Room
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="room-info" class="hidden mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-medium">Room ID:</h3>
                            <p id="room-id-display" class="text-blue-400 font-mono"></p>
                        </div>
                        <button onclick="copyRoomId()" class="p-2 bg-slate-200 rounded-md hover:bg-slate-300 transition">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="font-medium mb-2">Connected Users:</h3>
                        <div id="user-list" class="space-y-2"></div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button onclick="leaveRoom()" class="btn-danger flex-1 py-2 rounded-md">
                            <i class="fas fa-sign-out-alt mr-2"></i> Leave Room
                        </button>
                    </div>
                </div>
            </div>

            <!-- Center Panel - Walkie Talkie -->
            <div class="card p-6">
                <h2 class="text-xl font-semibold mb-4">Walkie Talkie</h2>
                
                <div class="flex flex-col items-center justify-center h-64">
                    <div id="local-audio-container" class="mb-6 w-full">
                        <div class="flex items-center mb-2">
                            <div class="user-avatar bg-blue-500 mr-2" id="local-avatar">YO</div>
                            <span>You</span>
                        </div>
                        <div class="audio-visualizer" id="local-visualizer">
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                            <div class="audio-bar"></div>
                        </div>
                    </div>
                    
                    <div id="remote-audios" class="w-full space-y-6"></div>
                    
                    <div class="mt-8 flex flex-col items-center">
                        <div id="ptt-container" class="mb-4">
                            <div id="walkie-btn" class="walkie-btn bg-blue-500" ontouchstart="startPTT()" ontouchend="endPTT()" onmousedown="startPTT()" onmouseup="endPTT()">
                                <i class="fas fa-microphone text-white text-2xl"></i>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500">Press and hold to talk</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-center space-x-4">
                    <button onclick="toggleSpeaker()" id="toggle-speaker-btn" class="btn-primary px-4 py-2 rounded-md">
                        <i class="fas fa-volume-up mr-2"></i> Speaker
                    </button>
                </div>
            </div>
            
            <!-- Right Panel - Chat -->
            <div class="card p-6">
                <h2 class="text-xl font-semibold mb-4">Group Chat</h2>
                
                <div id="chat-container" class="h-96 flex flex-col">
                    <div id="chat-messages" class="flex-1 overflow-y-auto mb-4 pr-2">
                        <!-- Messages will appear here -->
                    </div>
                    
                    <div id="typing-indicators" class="mb-2"></div>
                    
                    <div class="flex items-center">
                        <input id="chat-input" class="flex-grow px-4 py-2 bg-slate-100 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your message..." type="text" onkeydown="checkTyping()">
                        <button onclick="sendMessage()" class="btn-primary px-4 py-2 rounded-r-md">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    
                    <div class="flex justify-between mt-2">
                        <div class="media-buttons">
                            <label for="file-input" class="media-button">
                                <i class="fas fa-paperclip"></i>
                                <span>File</span>
                                <input type="file" id="file-input" accept="image/*, .pdf, .doc, .docx, video/*" style="display: none;">
                            </label>
                            <button class="media-button" onclick="openCamera()">
                                <i class="fas fa-camera"></i>
                                <span>Camera</span>
                            </button>
                            <button class="media-button" onclick="openImageSearch()">
                                <i class="fas fa-search"></i>
                                <span>Unsplash</span>
                            </button>
                        </div>
                        <div class="text-sm text-slate-500">
                            <span id="char-count">0/200</span>
                        </div>
                    </div>
                    
                    <!-- File upload progress -->
                    <div id="file-progress" class="progress-bar hidden">
                        <div id="progress-fill" class="progress-fill"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navbar -->
    <div class="mobile-navbar lg:hidden" id="mobile-navbar">
        <div class="nav-item-wrapper">
            <a href="#" class="nav-item active" onclick="showSection('room-info-section')">
                <i class="fas fa-users"></i>
                <span>Room</span>
            </a>
        </div>
        <div class="nav-item-wrapper">
            <a href="#" class="nav-item" onclick="showSection('walkie-section')">
                <i class="fas fa-microphone"></i>
                <span>Talk</span>
            </a>
        </div>
        <div class="nav-item-wrapper">
            <a href="#" class="nav-item" onclick="showSection('chat-section')">
                <i class="fas fa-comments"></i>
                <span>Chat</span>
                <span class="nav-badge hidden" id="chat-badge">0</span>
            </a>
        </div>
    </div>

    <!-- Notification Toast - Mobile Optimized -->
    <div id="notification" class="fixed bottom-4 right-4 left-4 sm:left-auto p-4 bg-blue-500 text-white rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50 max-w-full sm:max-w-xs text-sm sm:text-base"></div>

    <!-- New Message Notification - Mobile Optimized -->
    <div id="new-message-notification" class="new-message-notification fixed bottom-16 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white px-4 py-2 rounded-full shadow-md text-sm whitespace-nowrap sm:left-auto sm:right-4 sm:transform-none sm:bottom-4" onclick="scrollToNewMessage()">
        New message received
    </div>

    <!-- Unsplash Image Search Modal - Mobile Optimized -->
    <div id="unsplash-search-container" class="unsplash-search-container fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="unsplash-search-box bg-white rounded-lg w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col">
            <div class="unsplash-search-header flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold">Search for Images</h3>
                <button onclick="closeImageSearch()" class="p-2 rounded-full hover:bg-gray-100 text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="flex">
                    <input type="text" id="unsplash-search-input" class="flex-grow px-4 py-2 border rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-base" placeholder="Search for images...">
                    <button onclick="searchUnsplash()" class="bg-blue-500 text-white px-4 py-2 rounded-r-md text-base">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="unsplash-search-results overflow-y-auto p-4" id="unsplash-results">
                <!-- Images will appear here -->
            </div>
        </div>
    </div>

    <script>
        // Constants
        const PRE = "Transmitter";
        const SUF = "Umrah";
        const MAX_MESSAGE_LENGTH = 200;
        const UNSPLASH_ACCESS_KEY = 'bNf1fq2p6XENfQnZ6KAne40raJcwkzxFs4DWTvZHuY8'; 
        
        // App State
        let room_id;
        let peer = null;
        let local_stream;
        let connections = [];
        let dataConnections = [];
        let isMuted = false;
        let isSpeakerOn = true;
        let isPTTActive = false;
        let userColors = {};
        let typingUsers = {};
        let audioContext;
        let analyser;
        let dataArray;
        let animationId;
        let unreadMessagesCount = 0;
        let lastMessageSender = null;
        
        // Audio Elements
        const notifSound = new Audio("sound/notif.mp3");
        const leaveSound = new Audio("sound/leave.mp3");
        const joinSound = new Audio("sound/join.mp3");
        const messageSound = new Audio("sound/pesan.mp3");
        const pttStartSound = new Audio("sound/ptt_start.mp3");
        const pttEndSound = new Audio("sound/ptt_end.mp3");
        
        // DOM Elements
        const roomInput = document.getElementById("room-input");
        const roomIdDisplay = document.getElementById("room-id-display");
        const userList = document.getElementById("user-list");
        const chatMessages = document.getElementById("chat-messages");
        const chatInput = document.getElementById("chat-input");
        const charCount = document.getElementById("char-count");
        const notification = document.getElementById("notification");
        const localVisualizer = document.getElementById("local-visualizer");
        const walkieBtn = document.getElementById("walkie-btn");
        const statusIndicator = document.getElementById("status-indicator");
        const statusText = document.getElementById("status-text");
        const connectionStatus = document.getElementById("connection-status");
        const localAvatar = document.getElementById("local-avatar");
        const fileProgress = document.getElementById("file-progress");
        const progressFill = document.getElementById("progress-fill");
        const newMessageNotification = document.getElementById("new-message-notification");
        const unsplashSearchContainer = document.getElementById("unsplash-search-container");
        const unsplashSearchInput = document.getElementById("unsplash-search-input");
        const unsplashResults = document.getElementById("unsplash-results");
        
        // Initialize the app
        document.addEventListener("DOMContentLoaded", () => {
            // Show loading screen
            setTimeout(() => {
                document.getElementById("loading-screen").classList.add("opacity-0");
                setTimeout(() => {
                    document.getElementById("loading-screen").classList.add("hidden");
                    document.getElementById("app-container").classList.remove("hidden");
                }, 500);
            }, 1500);
            
            // Set up event listeners
            setupEventListeners();
            
            // Generate random user avatar
            const randomColor = getRandomColor();
            const initials = "YO"; // You can make this dynamic
            localAvatar.style.backgroundColor = randomColor;
            localAvatar.textContent = initials;
            
            // Initialize audio context for visualizer
            initAudioContext();
            
            // Set up character counter
            chatInput.addEventListener("input", updateCharCount);
            updateCharCount();
            
            // Prevent default touch behavior for PTT button
            walkieBtn.addEventListener("touchstart", (e) => {
                e.preventDefault();
            }, { passive: false });
            
            walkieBtn.addEventListener("touchend", (e) => {
                e.preventDefault();
            }, { passive: false });
            
            // Add IDs to main card sections for mobile navigation
            document.querySelectorAll('.card')[0].id = 'room-info-section';
            document.querySelectorAll('.card')[1].id = 'walkie-section';
            document.querySelectorAll('.card')[2].id = 'chat-section';
            
            // For mobile, initially show only the room info section
            if (window.innerWidth <= 768) {
                document.getElementById('walkie-section').classList.add('hidden');
                document.getElementById('chat-section').classList.add('hidden');
            }
        });
        
        function setupEventListeners() {
            // Info modal
            document.getElementById("info-btn").addEventListener("click", toggleInfoModal);
            
            // Keyboard PTT (hold V key to talk)
            document.addEventListener("keydown", (e) => {
                if (e.key === "v" && !isPTTActive && peer) {
                    startPTT();
                }
            });
            
            document.addEventListener("keyup", (e) => {
                if (e.key === "v" && isPTTActive) {
                    endPTT();
                }
            });
            
            // File input
            document.getElementById("file-input").addEventListener("change", handleFileUpload);
            
            // Handle Enter key for chat
            chatInput.addEventListener("keypress", (e) => {
                if (e.key === "Enter") {
                    sendMessage();
                }
            });
            
            // Listen for scroll events to mark messages as read
            chatMessages.addEventListener('scroll', function() {
                if (isChatAtBottom()) {
                    unreadMessagesCount = 0;
                    updateChatBadge(0);
                }
            });
        }
        
        function initAudioContext() {
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                analyser = audioContext.createAnalyser();
                analyser.fftSize = 32;
                const bufferLength = analyser.frequencyBinCount;
                dataArray = new Uint8Array(bufferLength);
                
                if (local_stream) {
                    const source = audioContext.createMediaStreamSource(local_stream);
                    source.connect(analyser);
                    visualizeAudio();
                }
            } catch (e) {
                console.error("Audio Context error:", e);
            }
        }
        
        function visualizeAudio() {
            if (!analyser) return;
            
            analyser.getByteFrequencyData(dataArray);
            
            const bars = localVisualizer.querySelectorAll(".audio-bar");
            for (let i = 0; i < bars.length; i++) {
                const value = dataArray[i] / 255;
                const height = value * 100;
                bars[i].style.transform = `scaleY(${height})`;
            }
            
            animationId = requestAnimationFrame(visualizeAudio);
        }
        
        function stopVisualization() {
            if (animationId) {
                cancelAnimationFrame(animationId);
            }
            
            const bars = localVisualizer.querySelectorAll(".audio-bar");
            bars.forEach(bar => {
                bar.style.transform = "scaleY(0.3)";
            });
        }
        
        function updateCharCount() {
            const count = chatInput.value.length;
            charCount.textContent = `${count}/${MAX_MESSAGE_LENGTH}`;
            
            if (count > MAX_MESSAGE_LENGTH * 0.9) {
                charCount.classList.add("text-yellow-500");
            } else {
                charCount.classList.remove("text-yellow-500");
            }
        }
        
        function createRoom() {
            let room = roomInput.value.trim();
            if (!room) {
                showNotification("Please enter a valid room ID", "error");
                return;
            }
            
            room_id = PRE + room + SUF;
            peer = new Peer(room_id);
            
            peer.on("open", (id) => {
                console.log("Peer Connected with ID: ", id);
                setupPeerConnection();
                showNotification("Room created successfully!", "success");
                
                // Set up local stream
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then((stream) => {
                        local_stream = stream;
                        setupLocalStream(stream);
                        updateConnectionStatus("connected");
                    })
                    .catch((err) => {
                        console.error("Error accessing microphone:", err);
                        showNotification("Microphone access denied", "error");
                    });
            });
            
            peer.on("error", (err) => {
                console.error("PeerJS error:", err);
                showNotification("Connection error: " + err.message, "error");
                updateConnectionStatus("error");
            });
        }
        
        function joinRoom() {
            let room = roomInput.value.trim();
            if (!room) {
                showNotification("Please enter a valid room ID", "error");
                return;
            }
            
            room_id = PRE + room + SUF;
            peer = new Peer();
            
            peer.on("open", (id) => {
                console.log("Connected with ID: " + id);
                setupPeerConnection();
                
                // Set up local stream
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then((stream) => {
                        local_stream = stream;
                        setupLocalStream(stream);
                        updateConnectionStatus("connected");
                        
                        // Connect to host
                        const conn = peer.connect(room_id);
                        setupDataConnection(conn);
                        
                        // Call host
                        const call = peer.call(room_id, stream);
                        setupMediaConnection(call);
                        
                        joinSound.play();
                        showNotification("Joined room successfully!", "success");
                    })
                    .catch((err) => {
                        console.error("Error accessing microphone:", err);
                        showNotification("Microphone access denied", "error");
                    });
            });
            
            peer.on("error", (err) => {
                console.error("PeerJS error:", err);
                showNotification("Connection error: " + err.message, "error");
                updateConnectionStatus("error");
            });
        }
        
        function setupPeerConnection() {
            // Hide entry modal and show room info
            document.getElementById("entry-modal").classList.add("hidden");
            document.getElementById("room-info").classList.remove("hidden");
            roomIdDisplay.textContent = room_id;
            
            // Set up peer event handlers
            peer.on("connection", (conn) => {
                setupDataConnection(conn);
            });
            
            peer.on("call", (call) => {
                call.answer(local_stream);
                setupMediaConnection(call);
            });
            
            peer.on("disconnected", () => {
                updateConnectionStatus("disconnected");
                showNotification("Connection lost. Attempting to reconnect...", "warning");
            });
            
            peer.on("close", () => {
                updateConnectionStatus("disconnected");
                showNotification("Connection closed", "info");
            });
        }
        
        function setupDataConnection(conn) {
            conn.on("open", () => {
                console.log("Data connection established with:", conn.peer);
                
                // Add to data connections array
                dataConnections.push(conn);
                
                // Generate color for this user
                userColors[conn.peer] = getRandomColor();
                
                // Add user to list
                updateUserList();
                
                // Set up message handler
                conn.on("data", (data) => {
                    const parsedData = JSON.parse(data);
                    
                    if (parsedData.type === "message") {
                        displayMessage(parsedData);
                    } else if (parsedData.type === "typing") {
                        handleTypingIndicator(parsedData);
                    } else if (parsedData.type === "user_update") {
                        updateUserList();
                    }
                });
                
                conn.on("close", () => {
                    console.log("Data connection closed:", conn.peer);
                    removeUserConnection(conn.peer);
                    leaveSound.play();
                    showNotification("User left: " + getShortPeerId(conn.peer), "info");
                });
                
                // Send user update to all connections
                broadcastUserUpdate();
            });
        }
        
        function setupMediaConnection(call) {
            call.on("stream", (stream) => {
                console.log("Media stream received from:", call.peer);
                addRemoteStream(stream, call.peer);
            });
            
            call.on("close", () => {
                console.log("Media connection closed:", call.peer);
                removeRemoteStream(call.peer);
            });
            
            // Add to connections array
            connections.push(call);
        }
        
        function setupLocalStream(stream) {
            const localAudio = document.createElement("audio");
            localAudio.srcObject = stream;
            localAudio.muted = true;
            localAudio.autoplay = true;
            localAudio.id = "local-audio";
            document.body.appendChild(localAudio);
            
            // Connect to audio visualizer
            if (audioContext) {
                const source = audioContext.createMediaStreamSource(stream);
                source.connect(analyser);
                visualizeAudio();
            }
        }
        
        function addRemoteStream(stream, peerId) {
            // Remove existing stream if any
            removeRemoteStream(peerId);
            
            // Create audio element
            const audioContainer = document.createElement("div");
            audioContainer.className = "remote-audio-container";
            audioContainer.id = `remote-audio-${peerId}`;
            
            // User info
            const userInfo = document.createElement("div");
            userInfo.className = "flex items-center mb-2";
            
            const avatar = document.createElement("div");
            avatar.className = "user-avatar mr-2";
            avatar.style.backgroundColor = userColors[peerId] || getRandomColor();
            avatar.textContent = getShortPeerId(peerId);
            
            const nameSpan = document.createElement("span");
            nameSpan.textContent = getShortPeerId(peerId);
            
            userInfo.appendChild(avatar);
            userInfo.appendChild(nameSpan);
            
            // Audio visualizer
            const visualizer = document.createElement("div");
            visualizer.className = "audio-visualizer";
            visualizer.innerHTML = `
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
                <div class="audio-bar"></div>
            `;
            
            // Audio element
            const audioElement = document.createElement("audio");
            audioElement.srcObject = stream;
            audioElement.autoplay = true;
            audioElement.className = "hidden";
            
            audioContainer.appendChild(userInfo);
            audioContainer.appendChild(visualizer);
            audioContainer.appendChild(audioElement);
            
            document.getElementById("remote-audios").appendChild(audioContainer);
            
            // Start visualization for remote audio
            setupRemoteVisualizer(stream, visualizer);
            
            // Play join sound and show notification
            if (peerId !== room_id) { // Don't notify for our own connection
                joinSound.play();
                showNotification("User joined: " + getShortPeerId(peerId), "info");
            }
        }
        
        function setupRemoteVisualizer(stream, visualizerElement) {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const analyser = audioContext.createAnalyser();
            analyser.fftSize = 32;
            const source = audioContext.createMediaStreamSource(stream);
            source.connect(analyser);
            
            const dataArray = new Uint8Array(analyser.frequencyBinCount);
            
            function visualize() {
                analyser.getByteFrequencyData(dataArray);
                
                const bars = visualizerElement.querySelectorAll(".audio-bar");
                for (let i = 0; i < bars.length; i++) {
                    const value = dataArray[i] / 255;
                    const height = value * 100;
                    bars[i].style.transform = `scaleY(${height})`;
                }
                
                requestAnimationFrame(visualize);
            }
            
            visualize();
        }
        
        function removeRemoteStream(peerId) {
            const existingAudio = document.getElementById(`remote-audio-${peerId}`);
            if (existingAudio) {
                existingAudio.remove();
            }
        }
        
        function removeUserConnection(peerId) {
            // Remove media connection
            connections = connections.filter(conn => conn.peer !== peerId);
            
            // Remove data connection
            dataConnections = dataConnections.filter(conn => conn.peer !== peerId);
            
            // Remove remote stream
            removeRemoteStream(peerId);
            
            // Update user list
            updateUserList();
        }
        
        function updateUserList() {
            userList.innerHTML = "";
            
            // Add local user (you)
            const localUserItem = document.createElement("div");
            localUserItem.className = "flex items-center py-2 px-3 bg-slate-100 rounded-md";
            
            const localAvatar = document.createElement("div");
            localAvatar.className = "user-avatar mr-2";
            localAvatar.style.backgroundColor = getRandomColor();
            localAvatar.textContent = "YO";
            
            const localName = document.createElement("span");
            localName.textContent = "You (Host)";
            
            localUserItem.appendChild(localAvatar);
            localUserItem.appendChild(localName);
            userList.appendChild(localUserItem);
            
            // Add remote users
            dataConnections.forEach(conn => {
                const userItem = document.createElement("div");
                userItem.className = "flex items-center py-2 px-3 bg-slate-100 rounded-md mt-2";
                
                const avatar = document.createElement("div");
                avatar.className = "user-avatar mr-2";
                avatar.style.backgroundColor = userColors[conn.peer] || getRandomColor();
                avatar.textContent = getShortPeerId(conn.peer);
                
                const name = document.createElement("span");
                name.textContent = getShortPeerId(conn.peer);
                
                userItem.appendChild(avatar);
                userItem.appendChild(name);
                userList.appendChild(userItem);
            });
        }
        
        function broadcastUserUpdate() {
            dataConnections.forEach(conn => {
                conn.send(JSON.stringify({
                    type: "user_update",
                    peer: peer.id
                }));
            });
        }
        
        function startPTT() {
            if (!local_stream || isPTTActive) return;
            
            isPTTActive = true;
            walkieBtn.classList.add("active");
            pttStartSound.play();
            
            // Unmute local stream
            local_stream.getAudioTracks().forEach(track => {
                track.enabled = true;
            });
        }
        
        function endPTT() {
            if (!isPTTActive) return;
            
            isPTTActive = false;
            walkieBtn.classList.remove("active");
            pttEndSound.play();
            
            // Mute local stream
            local_stream.getAudioTracks().forEach(track => {
                track.enabled = false;
            });
        }
        
        function togglePTT() {
            if (isPTTActive) {
                endPTT();
            } else {
                startPTT();
            }
        }
        
        function toggleSpeaker() {
            isSpeakerOn = !isSpeakerOn;
            const audioElements = document.querySelectorAll("audio");
            
            audioElements.forEach(audio => {
                audio.muted = !isSpeakerOn;
            });
            
            const btn = document.getElementById("toggle-speaker-btn");
            if (isSpeakerOn) {
                btn.innerHTML = '<i class="fas fa-volume-mute mr-2"></i> Mute All';
            } else {
                btn.innerHTML = '<i class="fas fa-volume-up mr-2"></i> Unmute All';
            }
        }
        
        function checkTyping() {
            if (!peer) return;
            
            // Send typing indicator
            dataConnections.forEach(conn => {
                conn.send(JSON.stringify({
                    type: "typing",
                    peer: peer.id,
                    isTyping: true
                }));
            });
            
            // Clear previous timeout if exists
            if (window.typingTimeout) {
                clearTimeout(window.typingTimeout);
            }
            
            // Set timeout to stop typing indicator
            window.typingTimeout = setTimeout(() => {
                dataConnections.forEach(conn => {
                    conn.send(JSON.stringify({
                        type: "typing",
                        peer: peer.id,
                        isTyping: false
                    }));
                });
            }, 2000);
        }
        
        function handleTypingIndicator(data) {
            const typingIndicators = document.getElementById("typing-indicators");
            
            if (data.isTyping) {
                typingUsers[data.peer] = true;
            } else {
                delete typingUsers[data.peer];
            }
            
            typingIndicators.innerHTML = "";
            
            const typingPeers = Object.keys(typingUsers);
            if (typingPeers.length > 0) {
                const indicator = document.createElement("div");
                indicator.className = "typing-indicator";
                
                const names = typingPeers.map(peerId => getShortPeerId(peerId)).join(", ");
                indicator.innerHTML = `
                    <span>${names} ${typingPeers.length > 1 ? "are" : "is"} typing</span>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                `;
                
                typingIndicators.appendChild(indicator);
            }
        }
        
        function sendMessage() {
            const message = chatInput.value.trim();
            const fileInput = document.getElementById("file-input");
            const file = fileInput.files[0];
            
            if (message === "" && !file) return;
            
            const data = {
                type: "message",
                sender: peer.id,
                message: message,
                color: userColors[peer.id] || getRandomColor(),
                timestamp: new Date().toISOString(),
                file: null,
                fileType: null,
                fileName: null
            };
            
            // Show sending indicator for local message
            const tempMessageId = "temp-" + Date.now();
            if (file) {
                displayLocalMessage(tempMessageId, "Sending file...", null, true);
            } else {
                displayLocalMessage(tempMessageId, message, null, false);
            }
            
            if (file) {
                // Show file upload progress
                fileProgress.classList.remove("hidden");
                progressFill.style.width = "0%";
                
                const reader = new FileReader();
                reader.onloadstart = function() {
                    progressFill.style.width = "10%";
                };
                
                reader.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentLoaded = Math.round((e.loaded / e.total) * 90);
                        progressFill.style.width = `${10 + percentLoaded}%`;
                    }
                };
                
                reader.onload = function(e) {
                    progressFill.style.width = "100%";
                    setTimeout(() => {
                        fileProgress.classList.add("hidden");
                    }, 500);
                    
                    data.file = e.target.result;
                    data.fileType = file.type;
                    data.fileName = file.name;
                    
                    // Send to all connections
                    sendDataToConnections(data, tempMessageId);
                };
                
                reader.onerror = function() {
                    fileProgress.classList.add("hidden");
                    removeTempMessage(tempMessageId);
                    showNotification("Failed to read file", "error");
                };
                
                reader.readAsDataURL(file);
            } else {
                // Send text message immediately
                sendDataToConnections(data, tempMessageId);
            }
            
            // Reset input
            chatInput.value = "";
            fileInput.value = "";
            updateCharCount();
        }
        
        function sendDataToConnections(data, tempMessageId) {
            let connectionsSent = 0;
            const totalConnections = dataConnections.length;
            
            if (totalConnections === 0) {
                // No connections, just display the message locally
                removeTempMessage(tempMessageId);
                displayMessage(data);
                messageSound.play();
                return;
            }
            
            dataConnections.forEach(conn => {
                conn.send(JSON.stringify(data));
                connectionsSent++;
                
                if (connectionsSent === totalConnections) {
                    // All connections have received the message
                    setTimeout(() => {
                        removeTempMessage(tempMessageId);
                        displayMessage(data);
                        messageSound.play();
                    }, 300);
                }
            });
        }
        
        function displayLocalMessage(id, message, fileInfo, isFile) {
            const messageElement = document.createElement("div");
            messageElement.id = id;
            messageElement.className = "message-bubble local-message";
            messageElement.style.backgroundColor = userColors[peer.id] || getRandomColor();
            
            const time = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
            
            if (isFile) {
                messageElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <strong>You</strong>
                        <span class="text-xs opacity-70">${time}</span>
                    </div>
                    <p class="mt-1">${message}</p>
                    <div class="sending-indicator">
                        <div class="sending-dot"></div>
                        <div class="sending-dot"></div>
                        <div class="sending-dot"></div>
                    </div>
                `;
            } else {
                messageElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <strong>You</strong>
                        <span class="text-xs opacity-70">${time}</span>
                    </div>
                    <p class="mt-1">${message}</p>
                    <div class="sending-indicator">
                        <div class="sending-dot"></div>
                        <div class="sending-dot"></div>
                        <div class="sending-dot"></div>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function removeTempMessage(id) {
            const tempMessage = document.getElementById(id);
            if (tempMessage) {
                tempMessage.remove();
            }
        }
        
        function handleFileUpload() {
            const fileInput = document.getElementById("file-input");
            if (fileInput.files.length > 0) {
                sendMessage();
            }
        }
        
        function displayMessage(data) {
            // Check if this is a new message from another user
            if (data.sender !== peer.id && !isChatAtBottom()) {
                unreadMessagesCount++;
                updateChatBadge(unreadMessagesCount);
                showNewMessageNotification(data.sender);
            }
            
            const messageElement = document.createElement("div");
            messageElement.className = `message-bubble ${data.sender === peer.id ? "local-message" : "remote-message"}`;
            messageElement.style.backgroundColor = data.color;
            
            const time = new Date(data.timestamp).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
            
            if (data.file) {
                let fileContent = "";
                
                if (data.fileType.startsWith("image")) {
                    // Add loading indicator for images
                    fileContent = `
                        <div class="relative">
                            <div class="receiving-indicator">
                                <div class="receiving-spinner"></div>
                            </div>
                            <img src="${data.file}" class="max-w-full h-auto rounded-md mt-2" alt="Shared image" 
                                 onload="this.parentElement.querySelector('.receiving-indicator').style.display = 'none'">
                        </div>
                    `;
                } else if (data.fileType.startsWith("video")) {
                    // Add loading indicator for videos
                    fileContent = `
                        <div class="relative">
                            <div class="receiving-indicator">
                                <div class="receiving-spinner"></div>
                            </div>
                            <video controls src="${data.file}" class="max-w-full h-auto rounded-md mt-2"
                                   oncanplay="this.parentElement.querySelector('.receiving-indicator').style.display = 'none'"></video>
                        </div>
                    `;
                } else {
                    fileContent = `
                        <div class="file-message">
                            <i class="fas fa-file mr-2"></i>
                            <a href="${data.file}" download="${data.fileName}" class="text-blue-400 hover:underline">${data.fileName}</a>
                        </div>
                    `;
                }
                
                messageElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <strong>${data.sender === peer.id ? "You" : getShortPeerId(data.sender)}</strong>
                        <span class="text-xs opacity-70">${time}</span>
                    </div>
                    ${data.message ? `<p class="mt-1">${data.message}</p>` : ""}
                    ${fileContent}
                `;
            } else {
                messageElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <strong>${data.sender === peer.id ? "You" : getShortPeerId(data.sender)}</strong>
                        <span class="text-xs opacity-70">${time}</span>
                    </div>
                    <p class="mt-1">${data.message}</p>
                `;
            }
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Play message sound if it's from another user
            if (data.sender !== peer.id) {
                messageSound.play();
            }
        }
        
        function leaveRoom() {
            if (connections.length > 0) {
                connections.forEach(conn => conn.close());
            }
            
            if (dataConnections.length > 0) {
                dataConnections.forEach(conn => conn.close());
            }
            
            if (peer) {
                peer.destroy();
            }
            
            if (local_stream) {
                local_stream.getTracks().forEach(track => track.stop());
            }
            
            // Stop audio visualization
            stopVisualization();
            
            // Play leave sound
            leaveSound.play();
            
            // Show leave notification
            showNotification("You left the room", "info");
            
            // Reset UI
            setTimeout(() => {
                document.getElementById("entry-modal").classList.remove("hidden");
                document.getElementById("room-info").classList.add("hidden");
                document.getElementById("remote-audios").innerHTML = "";
                document.getElementById("chat-messages").innerHTML = "";
                roomInput.value = "";
                updateConnectionStatus("disconnected");
            }, 500);
        }
        
        function copyRoomId() {
            navigator.clipboard.writeText(room_id).then(() => {
                showNotification("Room ID copied to clipboard!", "success");
            });
        }
        
        function showNotification(message, type) {
            notification.textContent = message;
            notification.className = "fixed bottom-4 right-4 p-4 rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50 max-w-xs";
            
            // Set color based on type
            if (type === "error") {
                notification.classList.add("bg-red-500");
            } else if (type === "success") {
                notification.classList.add("bg-green-500");
            } else if (type === "warning") {
                notification.classList.add("bg-yellow-500");
            } else {
                notification.classList.add("bg-blue-500");
            }
            
            // Show notification
            setTimeout(() => {
                notification.classList.remove("translate-y-10", "opacity-0");
                notification.classList.add("translate-y-0", "opacity-100");
            }, 10);
            
            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.remove("translate-y-0", "opacity-100");
                notification.classList.add("translate-y-10", "opacity-0");
            }, 3000);
        }
        
        function showNewMessageNotification(sender) {
            const senderName = getShortPeerId(sender);
            newMessageNotification.textContent = `New message from ${senderName}`;
            newMessageNotification.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                newMessageNotification.style.display = 'none';
            }, 5000);
        }
        
        function scrollToNewMessage() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
            newMessageNotification.style.display = 'none';
            unreadMessagesCount = 0;
            updateChatBadge(0);
        }
        
        function isChatAtBottom() {
            const chatContainer = chatMessages;
            return chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 50;
        }
        
        function updateConnectionStatus(status) {
            connectionStatus.classList.remove("hidden");
            
            switch (status) {
                case "connected":
                    statusIndicator.className = "w-3 h-3 rounded-full bg-green-500 mr-2";
                    statusText.textContent = "Connected";
                    break;
                case "disconnected":
                    statusIndicator.className = "w-3 h-3 rounded-full bg-red-500 mr-2";
                    statusText.textContent = "Disconnected";
                    break;
                case "error":
                    statusIndicator.className = "w-3 h-3 rounded-full bg-yellow-500 mr-2 pulse";
                    statusText.textContent = "Connection Error";
                    break;
                default:
                    connectionStatus.classList.add("hidden");
            }
        }
        
        function toggleInfoModal() {
            document.getElementById("info-modal").classList.toggle("hidden");
        }
        
        function getRandomColor() {
            const colors = [
                "#3B82F6", "#EF4444", "#10B981", "#F59E0B", 
                "#6366F1", "#EC4899", "#14B8A6", "#F97316"
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        
        function getShortPeerId(peerId) {
            return peerId.substring(0, 6);
        }
        
        // Mobile navigation functionality
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.card').forEach(card => {
                card.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // If showing chat section, mark messages as read
            if (sectionId === 'chat-section') {
                unreadMessagesCount = 0;
                updateChatBadge(0);
            }
        }
        
        function updateChatBadge(count) {
            const badge = document.getElementById('chat-badge');
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
        
        // Camera function
        function openCamera() {
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(stream => {
                    // Create a preview modal
                    const previewModal = document.createElement('div');
                    previewModal.className = 'fixed inset-0 bg-black bg-opacity-75 flex flex-col items-center justify-center z-50';
                    previewModal.innerHTML = `
                        <div class="bg-white rounded-lg p-4 max-w-md w-full">
                            <h3 class="text-lg font-semibold mb-2">Take a Photo</h3>
                            <video id="camera-preview" autoplay class="w-full h-auto mb-4"></video>
                            <div class="flex justify-between">
                                <button id="capture-btn" class="btn-primary px-4 py-2 rounded-md">
                                    <i class="fas fa-camera mr-2"></i> Capture
                                </button>
                                <button id="close-camera-btn" class="btn-danger px-4 py-2 rounded-md">
                                    <i class="fas fa-times mr-2"></i> Close
                                </button>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(previewModal);
                    
                    const video = previewModal.querySelector('#camera-preview');
                    video.srcObject = stream;
                    
                    // Capture button
                    previewModal.querySelector('#capture-btn').addEventListener('click', () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                        
                        // Convert to blob and create file
                        canvas.toBlob(blob => {
                            const file = new File([blob], 'photo.png', { type: 'image/png' });
                            
                            // Create a data transfer object to simulate file input
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            document.getElementById('file-input').files = dataTransfer.files;
                            
                            // Send the photo
                            sendMessage();
                            
                            // Stop the stream and remove modal
                            stream.getTracks().forEach(track => track.stop());
                            previewModal.remove();
                        }, 'image/png');
                    });
                    
                    // Close button
                    previewModal.querySelector('#close-camera-btn').addEventListener('click', () => {
                        stream.getTracks().forEach(track => track.stop());
                        previewModal.remove();
                    });
                })
                .catch(err => {
                    console.error('Error accessing camera:', err);
                    showNotification('Camera access denied', 'error');
                });
        }
        
        // Unsplash image search functions
        function openImageSearch() {
            unsplashSearchContainer.style.display = 'flex';
            unsplashSearchInput.focus();
        }
        
        function closeImageSearch() {
            unsplashSearchContainer.style.display = 'none';
            unsplashResults.innerHTML = '';
        }
        
        function searchUnsplash() {
            const query = unsplashSearchInput.value.trim();
            if (!query) return;
            
            unsplashResults.innerHTML = '<div class="col-span-full text-center py-4">Searching...</div>';
            
            // Note: In a production app, you should make this request through your backend
            // to keep your Unsplash access key secure
            fetch(`https://api.unsplash.com/search/photos?query=${query}&per_page=20&client_id=${UNSPLASH_ACCESS_KEY}`)
                .then(response => response.json())
                .then(data => {
                    unsplashResults.innerHTML = '';
                    
                    if (data.results.length === 0) {
                        unsplashResults.innerHTML = '<div class="col-span-full text-center py-4">No images found</div>';
                        return;
                    }
                    
                    data.results.forEach(photo => {
                        const photoItem = document.createElement('div');
                        photoItem.className = 'cursor-pointer';
                        photoItem.innerHTML = `
                            <img src="${photo.urls.small}" alt="${photo.alt_description || 'Unsplash image'}" 
                                 class="unsplash-image" data-regular="${photo.urls.regular}" 
                                 data-download="${photo.links.download_location}">
                            <div class="unsplash-photo-info">
                                <span>by ${photo.user.name}</span>
                            </div>
                        `;
                        
                        photoItem.addEventListener('click', () => {
                            selectUnsplashImage(photo.urls.regular);
                        });
                        
                        unsplashResults.appendChild(photoItem);
                    });
                })
                .catch(err => {
                    console.error('Unsplash API error:', err);
                    unsplashResults.innerHTML = '<div class="col-span-full text-center py-4">Error loading images</div>';
                });
        }
        
        function selectUnsplashImage(imageUrl) {
            // First show a loading indicator in the chat
            const tempMessageId = "temp-" + Date.now();
            displayLocalMessage(tempMessageId, "Downloading image...", null, true);
            
            // Fetch the image (in production, do this through your backend)
            fetch(imageUrl)
                .then(response => response.blob())
                .then(blob => {
                    const file = new File([blob], 'unsplash-image.jpg', { type: 'image/jpeg' });
                    
                    // Create a data transfer object to simulate file input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('file-input').files = dataTransfer.files;
                    
                    // Remove the loading message
                    removeTempMessage(tempMessageId);
                    
                    // Close the unsplash modal
                    closeImageSearch();
                    
                    // Send the image
                    sendMessage();
                })
                .catch(err => {
                    console.error('Error downloading image:', err);
                    removeTempMessage(tempMessageId);
                    showNotification('Failed to download image', 'error');
                });
        }
    </script>
</body>
</html>