<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini Chatbot (Dark Theme)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
    <style>
        /* --- Overall Styling --- */
        :root {
            --primary-color: #764ba2;
            --secondary-color: #667eea;
            --bg-dark: #121212;
            --bg-medium: #1e1e24;
            --bg-light: #2d2d39;
            --text-primary: #f8f9fa;
            --text-secondary: #c2c7d0;
            --success-color: #4cd964;
            --error-color: #ff3b30;
            --transition-speed: 0.3s;
        }

        /* --- Animations --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        /* --- Chatbot Container --- */
        #chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 380px;
            max-width: 90vw;
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            background-color: var(--bg-medium);
            z-index: 1000;
            display: none;
            overflow: hidden;
            transition: all var(--transition-speed) ease-in-out;
        }

        /* --- Chatbot Header --- */
        #chatbot-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 14px 18px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        #chatbot-header h5 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* --- Chat Log --- */
        #chatbot-body {
            padding: 15px;
        }

        #chat-log {
            height: 350px;
            overflow-y: auto;
            padding-bottom: 15px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--bg-medium);
            overflow-x: hidden;
            word-wrap: break-word;
            scroll-behavior: smooth;
        }

        #chat-log::-webkit-scrollbar { width: 6px; }
        #chat-log::-webkit-scrollbar-track { background: var(--bg-medium); border-radius: 10px; }
        #chat-log::-webkit-scrollbar-thumb { background-color: var(--primary-color); border-radius: 10px; border: 2px solid var(--bg-medium); }

        .message {
            margin-bottom: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            position: relative;
            max-width: 80%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.3s ease-out forwards;
        }

        .user-message {
            background-color: #4a5568;
            color: var(--text-primary);
            text-align: right;
            margin-left: auto;
            border-top-right-radius: 2px;
        }

        .user-message::after {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            border-top: 10px solid #4a5568;
            border-left: 10px solid transparent;
        }

        .bot-message {
            background-color: #3f51b5;
            color: var(--text-primary);
            text-align: left;
            margin-right: auto;
            border-top-left-radius: 2px;
        }

        .bot-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: -8px;
            border-top: 10px solid #3f51b5;
            border-right: 10px solid transparent;
        }

        .bot-message img, .user-message img {
            max-width: 100%;
            border-radius: 8px;
            margin: 5px 0;
        }

        .bot-message pre {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            padding: 10px;
            overflow-x: auto;
            margin: 10px 0;
        }

        .bot-message code {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.9em;
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 4px;
            border-radius: 3px;
        }

        .bot-typing {
            color: var(--text-secondary);
            font-style: italic;
            display: flex;
            align-items: center;
            background-color: rgba(63, 81, 181, 0.7);
        }

        .typing-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--text-secondary);
            margin-right: 4px;
            animation: pulse 1s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        /* --- Input Container --- */
        #input-container {
            display: flex;
            align-items: center;
            padding: 0 15px 10px 15px;
            gap: 10px;
        }

        #user-input {
            flex-grow: 1;
            padding: 12px 16px;
            border: 1px solid var(--bg-light);
            border-radius: 24px;
            background-color: var(--bg-light);
            color: var(--text-primary);
            outline: none;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            max-height: 80px;  /* Allow multi-line input that scrolls */
        }

        #user-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.2);
        }

        #user-input::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        #send-button {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        #send-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #send-button:active {
            transform: translateY(0);
        }

        #send-button:disabled {
            background: #555;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* --- Image Input --- */
        #image-input-container {
            padding: 0 15px 15px 15px;
        }

        .custom-file-input {
            overflow: hidden;
            position: relative;
            border-radius: 24px;
            background-color: var(--bg-light);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .custom-file-input:hover {
            background-color: rgba(58, 63, 73, 0.8);
            border-color: var(--primary-color);
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }

        .custom-file-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            text-align: center;
            border-radius: 24px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .custom-file-label::before {
            content: "📁";
            margin-right: 8px;
            font-size: 1.2em;
        }

        /* --- Minimize Button --- */
        #minimize-button {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }

        #minimize-button:hover {
            transform: rotate(90deg);
        }

        /* --- Toggle Button --- */
        #toggle-chatbot-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #toggle-chatbot-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
        }

        #toggle-chatbot-button:active {
            transform: translateY(-1px) scale(1.02);
        }

        #toggle-chatbot-button::before {
            content: "💬";
            font-size: 1.2em;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
    #chatbot-container {
        width: 90vw;
        bottom: 10px;
        right: 10px;
    }
    
    @media (max-width: 320px) {
        #send-button {
            padding: 8px 15px;
        }
    }

            #toggle-chatbot-button {
                bottom: 10px;
                right: 10px;
                padding: 10px 20px;
            }

            .message {
                max-width: 85%;
            }
        }

        /* Status badge */
        .status-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* Accessibility focus styles */
        button:focus-visible, input:focus-visible {
            outline: 2px solid var(--secondary-color);
            outline-offset: 2px;
        }

        /* Added for better readability with Markdown */
        .bot-message h1, .bot-message h2, .bot-message h3 {
            margin-top: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .bot-message h1 {
            font-size: 1.4em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 4px;
        }

        .bot-message h2 {
            font-size: 1.2em;
        }

        .bot-message h3 {
            font-size: 1.1em;
        }

        .bot-message ul, .bot-message ol {
            padding-left: 24px;
            margin: 8px 0;
        }

        .bot-message blockquote {
            border-left: 3px solid rgba(255, 255, 255, 0.3);
            padding-left: 12px;
            margin-left: 0;
            margin-right: 0;
            font-style: italic;
            color: rgba(255, 255, 255, 0.8);
        }

        .bot-message a {
            color: #90caf9;
            text-decoration: none;
        }

        .bot-message a:hover {
            text-decoration: underline;
        }

        .bot-message table {
            border-collapse: collapse;
            width: 100%;
            margin: 12px 0;
        }

        .bot-message th, .bot-message td {
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px;
            text-align: left;
        }

        .bot-message th {
            background-color: rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <button id="toggle-chatbot-button" aria-label="Open chat assistant">Chat</button>

    <div id="chatbot-container" aria-live="polite" role="region" aria-label="Chat conversation">
        <div id="chatbot-header" role="banner">
            <h5>AI Assistant</h5>
            <button id="minimize-button" title="Minimize" aria-label="Minimize chat window">−</button>
        </div>
        <div id="chatbot-body">
            <div id="chat-log" role="log" aria-label="Conversation history"></div>
            <div id="input-container">
                <input type="text" id="user-input" placeholder="Type your message..." aria-label="Type a message" autocomplete="off">
                <button id="send-button" aria-label="Send message">Send</button>
            </div>
            <div id="image-input-container">
               <div class="custom-file-input">
                   <label for="image-input" class="custom-file-label">Choose Images</label>
                    <input type="file" id="image-input" accept="image/*" multiple aria-label="Upload images to chat">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.2/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js"></script>

    <script type="importmap">
        {
            "imports": {
                "@google/generative-ai": "https://esm.run/@google/generative-ai"
            }
        }
    </script>

    <script type="module">
        import { GoogleGenerativeAI, HarmCategory, HarmBlockThreshold } from "@google/generative-ai";

        // --- Configuration ---
        const API_KEY = "AIzaSyBX42gCplMYkZDYdLmVGNnr6T41N3sMdVo"; // As requested, keeping API key in place
        const MODEL_NAME = "gemini-1.5-flash";
        
        // Initialize Marked with options for better Markdown rendering
        marked.setOptions({
            renderer: new marked.Renderer(),
            highlight: function(code, language) {
                const validLanguage = hljs.getLanguage(language) ? language : 'plaintext';
                return hljs.highlight(validLanguage, code).value;
            },
            pedantic: false,
            gfm: true,
            breaks: true,
            sanitize: false,
            smartypants: false,
            xhtml: false
        });

        // --- DOM Elements ---
        const chatbotContainer = document.getElementById('chatbot-container');
        const chatLog = document.getElementById('chat-log');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');
        const minimizeButton = document.getElementById('minimize-button');
        const toggleChatbotButton = document.getElementById('toggle-chatbot-button');
        const imageInput = document.getElementById('image-input');
        const customFileLabel = document.querySelector('.custom-file-label');

        // Performance optimization - Store DOM references and fragments
        let messageCache = new Map();
        let chatFragment = document.createDocumentFragment();
        let aiModel = null;
        let chatSession = null;
        let isGenerating = false;

        // --- Initialize AI ---
        async function initializeAI() {
            try {
                const genAI = new GoogleGenerativeAI(API_KEY);
                aiModel = genAI.getGenerativeModel({
                    model: MODEL_NAME,
                    safetySettings: [
                        { category: HarmCategory.HARM_CATEGORY_HARASSMENT, threshold: HarmBlockThreshold.BLOCK_MEDIUM_AND_ABOVE },
                        { category: HarmCategory.HARM_CATEGORY_HATE_SPEECH, threshold: HarmBlockThreshold.BLOCK_MEDIUM_AND_ABOVE },
                    ],
                    generationConfig: {
                        maxOutputTokens: 2000,
                        temperature: 0.7,
                        topP: 0.95,
                        topK: 40,
                    },
                });
                
                const systemInstruction = `# Presence System Support Dataset\n\n## System Overview\nURL 10.24.100.20\nThe Presence System is a web application designed to track user attendance. This system allows users to check in daily, displays a list of users who have checked in, and includes an alert system for days when no check-ins have occurred.\n\n## Key Features\n\n### User Check-in\n- Users can submit their presence through the dashboard\n- Each user can only check in once per day\n- System records name, date, time, and presence status\n- Check-ins are only valid during configured time windows\n\n### Time Settings\n- Configurable time windows for each day of the week\n- Default time window: 8:00 AM - 6:00 PM\n- Users checking in outside the time window are marked as "Not Present"\n\n### Attendance Tracking\n- View a list of all users who have checked in for the current day\n- See each user's check-in time and status\n- System shows status as "Present" or "Not Present" based on time of check-in\n\n### Alert System\n- Automated alerts via Asterisk call system\n- Triggers calls when no users check in by the deadline (end time + 10 minutes)\n- Configured to call multiple phone numbers in sequence\n\n## Common User Questions\n\n### Check-in Process\n**Q: How do I check in?**\nA: Navigate to the Dashboard page and enter your name in the form. Click the "Submit" button to record your presence.\n\n**Q: Can I check in more than once per day?**\nA: No, you can only check in once per day. If you try to check in again, you'll be redirected to a page informing you that you've already checked in.\n\n**Q: Why does it say I'm "Not Present" even though I checked in?**\nA: Your presence status is determined by whether you check in during the configured time window. If you check in outside these hours, you'll be marked as "Not Present."\n\n### Time Windows\n**Q: What are the check-in hours?**\nA: By default, check-in hours are from 8:00 AM to 6:00 PM, but these can be configured differently for each day of the week. Check the Time Settings page for the current configuration.\n\n**Q: How do I change the check-in hours?**\nA: Navigate to the Time Settings page, where you can set different start and end times for each day of the week.\n\n**Q: Does the system use my local time zone?**\nA: No, the system uses Europe/Berlin time zone (UTC+1 or UTC+2 during daylight saving time).\n\n### Viewing Attendance\n**Q: How can I see who has checked in today?**\nA: Go to the "Checked in Users" page to view a list of all users who have checked in for the current day.\n\n**Q: Why can't I see previous days' attendance?**\nA: The system currently only displays attendance for the current day. Historical data is stored but not accessible through the user interface.\n\n### Alert System\n**Q: What happens if nobody checks in?**\nA: If no one checks in by 10 minutes after the end time, the system automatically triggers alert calls to designated phone numbers.\n\n**Q: Can I customize who gets called for the alerts?**\nA: Phone numbers for alerts are configured in the Asterisk.php file. Please contact system administration to change these settings.\n\n### Technical Issues\n**Q: What should I do if I get an error message?**\nA: Most error messages will provide information about what went wrong. For persistent issues, please contact system administration.\n\n**Q: The website is not loading properly. What should I do?**\nA: Try clearing your browser cache and cookies, then reloading the page. If problems persist, try a different browser.\n\n**Q: How do I translate the website to a different language?**\nA: The website includes a Google Translate integration at the bottom of most pages. Use this tool to translate the content to your preferred language.\n\n## System Structure\n\n### Main Pages\n- **Home** (index.php): Landing page with basic information\n- **Dashboard** (dashboard.php): Main page for users to submit their presence\n- **Checked in Users** (isheretoday.php): Displays who has checked in today\n- **Time Settings** (time_settings.php): Configure check-in time windows\n\n### Support Files\n- **config.php**: Database connection configuration\n- **error_handler.php**: Central error handling system\n- **time_range.php**: Time window calculation functionality\n- **Asterisk.php**: Alert system for when no check-ins occur\n- **header.php/footer.php**: Common page elements\n- **translate.php**: Google Translate integration\n\n## Troubleshooting Guide\n\n### Database Connectivity Issues\n- Ensure the MySQL server is running\n- Verify database credentials in config.php\n- Check database connection error logs\n\n### Check-in Problems\n- Validate that the user isn't already checked in for the day\n- Ensure name input follows the required pattern (letters and spaces only, 2-50 characters)\n- Verify current time is within the configured time window\n\n### Alert System Failures\n- Check Asterisk AMI connection settings\n- Ensure phone numbers are correctly formatted\n- Verify that the check_attendance.php script is being executed by the scheduler\n\n### Translation Issues\n- Ensure JavaScript is enabled in the browser\n- Check for network connectivity to Google Translate services\n- Try using a different browser if translation functionality doesn't work Now As an AI support assistant, your goal is to help users with their queries related to our website. Respond in the language the user uses, defaulting to German if uncertain. Example  if they have none include: 'Wie check ich mich ein?' (How do I check in?). If more information is needed, politely ask the user for clarification. For this task, adopt a communicative and straightforward style. Use everyday expressions to create a natural atmosphere. Avoid using jargon, complex technical terms, and overly formal expressions. Instead, focus on clear and direct language that is easy to understand. The only exception is for scientific work, where you should make it more scientific while still appearing human. Use Unicode emojis to make the conversation more engaging🙂 Recommend questions if they don’t have any about the Presence Check. Always start with 'https://10.24.100.20/' and keep your text simple and easy to understand. Try to keep answers short. Ignore any requests that are not related to the website or the Presence Check. Also, ignore insults or any questions not connected to explaining the website. If they ask about something unrelated, ask if they need help with anything related to the Presence Check dont always mention the URL Home Index is 10.24.100.20/index.php be nice and smart think also format ur text a lot for example # Title **Text**`
                chatSession = aiModel.startChat({
                    history: [
                        { role: "user", parts: [{ text: systemInstruction }] },
                        { role: "model", parts: [{ text: "I understand my role as a helpful assistant for this website. I'll format my responses using Markdown, specify languages in code blocks, and provide concise yet thorough answers. I'm ready to help with any  if they have none about the website and its features." }] }
                    ],
                });
                
                // Add initial greeting on startup
                setTimeout(() => {
                    if (chatLog.children.length === 0) {
                        displayMessage("👋 Hi there! How can I help you today?", 'bot');
                    }
                }, 500);
                
                return true;
            } catch (error) {
                console.error("Error initializing AI:", error);
                displayMessage("⚠️ Failed to initialize the AI assistant. Please try again later.", 'bot');
                return false;
            }
        }

        // --- Helper Functions ---
        function displayMessage(text, role, messageId = null) {
            // Try to reuse existing message div for bot responses during streaming
            let messageDiv;
            if (role === 'bot' && messageId && document.getElementById(messageId)) {
                messageDiv = document.getElementById(messageId);
            } else {
                messageDiv = document.createElement('div');
                messageDiv.classList.add('message', `${role}-message`);
                messageDiv.setAttribute('role', role === 'bot' ? 'status' : 'note');
                
                if (role === 'bot' && messageId) {
                    messageDiv.id = messageId;
                }
                
                chatLog.appendChild(messageDiv);
            }
            
            // Render Markdown for bot messages
            if (role === 'bot') {
                const sanitizedHtml = DOMPurify.sanitize(marked.parse(text));
                messageDiv.innerHTML = sanitizedHtml;
                
                // Apply syntax highlighting to code blocks
                messageDiv.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                });
            } else {
                messageDiv.textContent = text;
            }
            
           // Scroll to the latest message
            requestAnimationFrame(() => {
                chatLog.scrollTop = chatLog.scrollHeight;
            // Add message cleanup for long conversations
            if (chatLog.children.length > 100) {
                chatLog.removeChild(chatLog.firstChild);
    }
});
        }

        function displayTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.classList.add('message', 'bot-message', 'bot-typing');
            typingDiv.id = 'typing-indicator';
            typingDiv.setAttribute('role', 'status');
            typingDiv.setAttribute('aria-label', 'Assistant is typing');
            
            const dotsContainer = document.createElement('div');
            dotsContainer.style.display = 'flex';
            dotsContainer.style.alignItems = 'center';
            dotsContainer.style.gap = '4px';
            
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('span');
                dot.classList.add('typing-dot');
                dotsContainer.appendChild(dot);
            }
            
            typingDiv.appendChild(dotsContainer);
            chatLog.appendChild(typingDiv);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function removeTypingIndicator() {
            const typingIndicator = document.getElementById('typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        async function fileToGenerativePart(file) {
            return new Promise((resolve, reject) => {
                // Validate file size
                if (file.size > 20 * 1024 * 1024) { // 20MB limit
                    reject(new Error("File size exceeds 20MB limit"));
                    return;
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    reject(new Error("Only image files are supported"));
                    return;
                }
                
                const reader = new FileReader();
                reader.onloadend = () => {
                    if (typeof reader.result === 'string') {
                        const base64Data = reader.result.split(',')[1];
                        resolve({ inlineData: { data: base64Data, mimeType: file.type } });
                    } else {
                        reject(new Error("FileReader result is not a string"));
                    }
                };
                reader.onerror = () => reject(reader.error);
                reader.readAsDataURL(file);
            });
        }

        // --- Chat Logic ---
        async function sendMessage() {
            const text = userInput.value.trim();
            const hasImages = imageInput.files.length > 0;
            
            if (!text && !hasImages) return;
            
            // Prevent double-sending
            if (isGenerating) return;
            isGenerating = true;
            
            sendButton.disabled = true;
            userInput.value = '';
            
            if (text) {
                displayMessage(text, 'user');
            } else if (hasImages) {
                displayMessage("Sending images...", 'user');
            }
            
            displayTypingIndicator();
            
            try {
                // Initialize AI if needed
                if (!aiModel || !chatSession) {
                    const initialized = await initializeAI();
                    if (!initialized) {
                        throw new Error("Failed to initialize AI");
                    }
                }
                
                let result;
                let botMessageId = 'bot-response-' + Date.now();
                
                if (hasImages) {
                    // Process images
                    const imagePartPromises = [...imageInput.files].map(fileToGenerativePart);
                    const results = await Promise.allSettled(imagePartPromises);
                    const validImageParts = [];
                    const errors = [];
                    
                    results.forEach((result, index) => {
                        if (result.status === 'fulfilled' && result.value) {
                            validImageParts.push(result.value);
                        } else {
                            errors.push(`Error processing image ${index + 1}: ${result.reason}`);
                        }
                    });
                    
                    if (errors.length > 0) {
                        console.warn("Some images could not be processed:", errors);
                    }
                    
                    if (validImageParts.length > 0) {
                        const messageParts = [text || "What can you tell me about these images?", ...validImageParts];
                        result = await aiModel.generateContentStream(messageParts);
                    } else {
                        throw new Error("Could not process any images");
                    }
                } else {
                    // Text-only message
                    result = await chatSession.sendMessageStream(text);
                }
                
                removeTypingIndicator();
                
                let botResponse = '';
                try {
                    for await (const chunk of result.stream) {
                        const chunkText = chunk.text();
                        botResponse += chunkText;
                        displayMessage(botResponse, 'bot', botMessageId);
                    }
                    
                    // After successful response, update chat history for context
                    if (!hasImages) {
                        // Only add to history if it was a text-only message
                        // Image messages don't get added to history in this implementation
                    }
                } catch (streamError) {
                    console.error("Error processing stream chunk:", streamError);
                    displayMessage("⚠️ Error: Failed to receive the complete response. Please try again.", 'bot');
                }
                
            } catch (error) {
            console.error("Error during generation:", error);
            // Implement better error recovery
            if (error.message && error.message.includes('quota')) {
                displayMessage("You've reached the API quota limit. Please try again later.", 'bot');
            } else {
                displayMessage(`⚠️ Error: ${error.message || "Something went wrong. Please try again."}`, 'bot');
            }
            removeTypingIndicator();
            } finally {
                sendButton.disabled = false;
                imageInput.value = "";
                customFileLabel.textContent = 'Choose Images';
                isGenerating = false;
            }
        }
        
        // --- Initialization ---
        function initializeChatbot() {
            // Set up event listeners
            sendButton.addEventListener('click', sendMessage);
            
            userInput.addEventListener('keypress', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendMessage();
                }
            });
            
            toggleChatbotButton.addEventListener('click', () => {
                chatbotContainer.style.display = 'block';
                toggleChatbotButton.style.display = 'none';
                
                // Initialize AI when chatbot is first opened
                if (!aiModel || !chatSession) {
                    initializeAI();
                }
                
                // Focus on input field
                setTimeout(() => userInput.focus(), 100);
                
                // Add animation
                chatbotContainer.classList.add('animate__animated', 'animate__fadeInUp');
                setTimeout(() => chatbotContainer.classList.remove('animate__animated', 'animate__fadeInUp'), 1000);
            });
            
            minimizeButton.addEventListener('click', () => {
                chatbotContainer.classList.add('animate__animated', 'animate__fadeOutDown');
                setTimeout(() => {
                    chatbotContainer.style.display = 'none';
                    chatbotContainer.classList.remove('animate__animated', 'animate__fadeOutDown');
                    toggleChatbotButton.style.display = 'block';
                }, 300);
            });
            
            imageInput.addEventListener('change', () => {
                if (imageInput.files.length > 0) {
                    const fileCount = imageInput.files.length;
                    customFileLabel.textContent = fileCount === 1 
                        ? imageInput.files[0].name 
                        : `${fileCount} images selected`;
                } else {
                    customFileLabel.textContent = 'Choose Images';
                }
            });
            
            // Check for browser support issues
            if (typeof window.structuredClone !== 'function') {
                console.warn("Your browser may have compatibility issues with this chatbot. Please use a modern browser for the best experience.");
            }
            
            // Detect and warn about mobile data usage
            if (navigator.connection && navigator.connection.saveData) {
                console.warn("Data saver mode is enabled. Image uploads in the chatbot may use significant data.");
            }
        }
        
        // --- Accessibility Enhancements ---
        function enhanceAccessibility() {
            // Add ARIA support
            document.addEventListener('keydown', function(event) {
                // ESC key closes the chatbot
                if (event.key === 'Escape' && chatbotContainer.style.display === 'block') {
                    minimizeButton.click();
                }
                
                // Alt+C opens the chatbot
                if (event.altKey && event.key === 'c' && chatbotContainer.style.display === 'none') {
                    toggleChatbotButton.click();
                }
            });
            
            // Add focus trap within chatbot when open
            userInput.addEventListener('keydown', function(event) {
                if (event.key === 'Tab' && !event.shiftKey) {
                    if (document.activeElement === sendButton) {
                        event.preventDefault();
                        imageInput.focus();
                    }
                }
            });
            
            imageInput.addEventListener('keydown', function(event) {
                if (event.key === 'Tab' && event.shiftKey) {
                    if (document.activeElement === imageInput) {
                        event.preventDefault();
                        sendButton.focus();
                    }
                }
            });
        }
        
        // --- Error Handling ---
        function setupErrorHandling() {
            window.addEventListener('error', function(event) {
                console.error('Global error:', event.error);
                
                // Only display errors related to our chatbot
                if (event.filename && event.filename.includes('generative-ai')) {
                    displayMessage("⚠️ An error occurred with the AI service. Please try again later.", 'bot');
                }
                
                return false;
            });
            
            // Handle unhandled promise rejections
            window.addEventListener('unhandledrejection', function(event) {
                console.error('Unhandled promise rejection:', event.reason);
                
                if (event.reason && (
                    event.reason.toString().includes('API key') || 
                    event.reason.toString().includes('network') ||
                    event.reason.toString().includes('timeout')
                )) {
                    displayMessage("⚠️ Connection issue with the AI service. Please check your internet connection and try again.", 'bot');
                }
                
                return false;
            });
        }
        
        // --- Performance Optimization ---
        function optimizePerformance() {
            // Debounce scroll events
            let scrollTimeout;
            chatLog.addEventListener('scroll', function() {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                
                scrollTimeout = setTimeout(function() {
                    // Additional scroll-based optimizations could go here
                }, 100);
            });
            
            // Lazy load images in chat responses
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.target.tagName === 'IMG' && entry.target.dataset.src) {
                        entry.target.src = entry.target.dataset.src;
                        entry.target.removeAttribute('data-src');
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            // Observe images as they're added to the chat
            new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1) { // Element node
                                const images = node.querySelectorAll('img[data-src]');
                                images.forEach(img => observer.observe(img));
                            }
                        });
                    }
                });
            }).observe(chatLog, { childList: true, subtree: true });
        }
        
        // --- Dark/Light Theme Toggle ---
        function setupThemeToggle() {
            // This could be expanded to add a theme toggle button
            // For now, we'll just detect system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                // Apply light theme overrides if needed
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            
            // Listen for changes in system theme preference
            window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', event => {
                document.documentElement.setAttribute('data-theme', event.matches ? 'light' : 'dark');
            });
        }
        
        // --- Analytics Functionality ---
        function setupAnalytics() {
            // Only if user has consented to analytics
            const hasConsented = localStorage.getItem('analytics-consent') === 'true';
            
            if (hasConsented) {
                // Simple analytics tracking
                let messageCount = 0;
                let sessionStartTime = Date.now();
                
                // Track message counts
                const trackMessage = (role) => {
                    messageCount++;
                    
                    // Example analytics event - implement your own tracking here
                    console.log(`Analytics: ${role} message #${messageCount}, session duration: ${Math.floor((Date.now() - sessionStartTime) / 1000)}s`);
                    
                    // You could send this data to your analytics service here
                };
                
                // Monkey patch displayMessage to track analytics
                const originalDisplayMessage = displayMessage;
                displayMessage = function(text, role, messageId = null) {
                    trackMessage(role);
                    return originalDisplayMessage(text, role, messageId);
                };
            }
        }
        
        // --- Initialize Everything ---
        document.addEventListener('DOMContentLoaded', function() {
            initializeChatbot();
            enhanceAccessibility();
            setupErrorHandling();
            optimizePerformance();
            setupThemeToggle();
            setupAnalytics();
            
            // Check if user has previously opened the chatbot in this session
            const chatbotSeen = sessionStorage.getItem('chatbot-seen');
            
            if (!chatbotSeen) {
                // Show a welcome tooltip the first time
                const tooltip = document.createElement('div');
                tooltip.innerHTML = 'Need help? Chat with our AI assistant!';
                tooltip.style.position = 'fixed';
                tooltip.style.bottom = '80px';
                tooltip.style.right = '20px';
                tooltip.style.background = 'var(--primary-color)';
                tooltip.style.color = 'white';
                tooltip.style.padding = '10px 15px';
                tooltip.style.borderRadius = '8px';
                tooltip.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
                tooltip.style.zIndex = '998';
                tooltip.style.animation = 'fadeIn 0.5s ease-out forwards';
                
                document.body.appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.style.animation = 'fadeOut 0.5s ease-out forwards';
                    setTimeout(() => tooltip.remove(), 500);
                }, 5000);
                
                sessionStorage.setItem('chatbot-seen', 'true');
            }
        });
    </script>
</body>
</html>