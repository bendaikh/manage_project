<template>
  <div class="ai-chatbot">
    <!-- Floating Chat Button -->
    <button
      @click="toggleChat"
      class="fixed bottom-6 right-6 z-50 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 transform hover:scale-110"
      :class="{ 'bg-red-500 hover:bg-red-600': isChatOpen }"
    >
      <svg
        v-if="!isChatOpen"
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
        />
      </svg>
      <svg
        v-else
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </button>

    <!-- Chat Popup -->
    <div
      v-if="isChatOpen"
      class="fixed bottom-20 right-6 z-40 w-96 h-[500px] bg-white rounded-lg shadow-2xl border border-gray-200 flex flex-col"
    >
      <!-- Chat Header -->
      <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold">AI Assistant</h3>
            <p class="text-xs text-blue-100">Ask me anything about your business</p>
          </div>
        </div>
        <button
          @click="toggleChat"
          class="text-blue-100 hover:text-white transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Chat Messages -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <p class="font-medium">Hello! I'm your AI assistant.</p>
          <p class="text-sm mt-1">Ask me about orders, customers, finances, or anything else!</p>
        </div>

        <!-- Messages -->
        <div
          v-for="(message, index) in messages"
          :key="index"
          :class="[
            'flex',
            message.type === 'user' ? 'justify-end' : 'justify-start'
          ]"
        >
          <div
            :class="[
              'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
              message.type === 'user'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-800'
            ]"
          >
            <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
            <p class="text-xs mt-1 opacity-70">
              {{ formatTime(message.timestamp) }}
            </p>
          </div>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="flex justify-start">
          <div class="bg-gray-100 text-gray-800 max-w-xs lg:max-w-md px-4 py-2 rounded-lg">
            <div class="flex items-center space-x-2">
              <div class="flex space-x-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
              <span class="text-sm text-gray-600">AI is thinking...</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Chat Input -->
      <div class="border-t border-gray-200 p-4">
        <form @submit.prevent="sendMessage" class="flex space-x-2">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Type your question..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :disabled="isLoading"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || isLoading"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AiChatbot',
  data() {
    return {
      isChatOpen: false,
      messages: [],
      newMessage: '',
      isLoading: false
    }
  },
  methods: {
    toggleChat() {
      this.isChatOpen = !this.isChatOpen
      if (this.isChatOpen) {
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      }
    },
    
    async sendMessage() {
      if (!this.newMessage.trim() || this.isLoading) return
      
      const userMessage = this.newMessage.trim()
      this.messages.push({
        type: 'user',
        content: userMessage,
        timestamp: new Date()
      })
      
      this.newMessage = ''
      this.isLoading = true
      
      try {
        const response = await fetch('/api/ai-chat', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ message: userMessage })
        })
        
        if (!response.ok) {
          throw new Error('Failed to get response from AI')
        }
        
        const data = await response.json()
        
        this.messages.push({
          type: 'ai',
          content: data.response,
          timestamp: new Date()
        })
      } catch (error) {
        console.error('Error sending message:', error)
        this.messages.push({
          type: 'ai',
          content: 'Sorry, I encountered an error while processing your request. Please try again.',
          timestamp: new Date()
        })
      } finally {
        this.isLoading = false
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      }
    },
    
    scrollToBottom() {
      if (this.$refs.messagesContainer) {
        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight
      }
    },
    
    formatTime(timestamp) {
      return new Date(timestamp).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    }
  }
}
</script>

<style scoped>
.ai-chatbot {
  font-family: 'Inter', sans-serif;
}

/* Custom scrollbar for chat messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style> 