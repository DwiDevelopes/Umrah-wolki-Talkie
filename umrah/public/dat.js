// Constants
        const PRE = "Transmitter";
        const SUF = "Umrah";
        const MAX_MESSAGE_LENGTH = 200;
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
        }

        // Initialize mobile sections by adding IDs to your cards
        document.addEventListener('DOMContentLoaded', function() {
            // Add IDs to your main card sections
            document.querySelectorAll('.card')[0].id = 'room-info-section';
            document.querySelectorAll('.card')[1].id = 'walkie-section';
            document.querySelectorAll('.card')[2].id = 'chat-section';
            
            // For mobile, initially show only the room info section
            if (window.innerWidth <= 768) {
                document.getElementById('walkie-section').classList.add('hidden');
                document.getElementById('chat-section').classList.add('hidden');
            }
        });

        // Update chat badge count
        function updateChatBadge(count) {
            const badge = document.getElementById('chat-badge');
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
                
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
            
            // Load theme preference
            loadTheme();
            
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
        });
        
        function setupEventListeners() {
            // Theme toggle
            document.getElementById("theme-toggle").addEventListener("click", toggleTheme);
            
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
            localUserItem.className = "flex items-center py-2 px-3 bg-slate-700 rounded-md";
            
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
                userItem.className = "flex items-center py-2 px-3 bg-slate-700 rounded-md mt-2";
                
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
        
        function toggleMicrophone() {
            isMuted = !isMuted;
            local_stream.getAudioTracks().forEach(track => {
                track.enabled = !isMuted;
            });
            
            const btn = document.getElementById("toggle-microphone-btn");
            if (isMuted) {
                btn.innerHTML = '<i class="fas fa-microphone mr-2"></i> Unmute';
            } else {
                btn.innerHTML = '<i class="fas fa-microphone-slash mr-2"></i> Mute';
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
        
        function toggleTheme() {
            const body = document.body;
            const isLightMode = body.classList.toggle("light-mode");
            localStorage.setItem("theme", isLightMode ? "light" : "dark");
            
            const themeToggle = document.getElementById("theme-toggle");
            if (isLightMode) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }
        
        function loadTheme() {
            const savedTheme = localStorage.getItem("theme");
            if (savedTheme === "light") {
                document.body.classList.add("light-mode");
                document.getElementById("theme-toggle").innerHTML = '<i class="fas fa-sun"></i>';
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