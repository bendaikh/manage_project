# 🤖 AI Chatbot Integration

A modern, AI-powered chatbot for your Laravel business management system.

## ✨ Features

- **Floating Chat Button**: Always accessible in the bottom-right corner
- **Smart Responses**: Powered by OpenAI GPT-4
- **Business Intelligence**: Answers questions about orders, finances, products, and users
- **Secure**: Authentication required, data sanitization
- **Responsive**: Works on desktop and mobile devices

## 🚀 Quick Setup

### 1. Add OpenAI API Key
Add to your `.env` file:
```env
OPENAI_API_KEY=your_openai_api_key_here
```

### 2. Test the Integration
```bash
# Test from command line
php artisan ai:test "How many orders do we have?"

# Run tests
php artisan test --filter=AiChatTest
```

### 3. Access the Chatbot
- Log into your application
- Look for the chat button in the bottom-right corner
- Click to open the chat interface

## 💬 Example Questions

Users can ask natural language questions like:
- "How many orders did client John Smith make last month?"
- "What are today's cash-on-delivery totals?"
- "Show me the total income for this month"
- "Which products are most popular?"
- "How many agents do we have?"

## 🔧 Files Created/Modified

### New Files:
- `resources/js/components/AiChatbot.vue` - Frontend chat component
- `app/Http/Controllers/AiChatController.php` - Backend API controller
- `tests/Feature/AiChatTest.php` - Test suite
- `app/Console/Commands/TestAiChat.php` - CLI test command
- `AI_CHATBOT_SETUP.md` - Detailed setup guide

### Modified Files:
- `routes/api.php` - Added AI chat route
- `routes/web.php` - Added AI chat route with auth
- `resources/js/components/App.vue` - Integrated chatbot component

## 🛡️ Security

- **Authentication Required**: Only logged-in users can access
- **Data Sanitization**: Sensitive data is filtered before sending to OpenAI
- **CSRF Protection**: All requests are protected
- **Rate Limiting**: Can be easily added if needed

## 🎨 Customization

### Styling
The chatbot uses Tailwind CSS. Modify classes in `AiChatbot.vue` to match your brand.

### AI Behavior
Edit the `buildPrompt()` method in `AiChatController.php` to customize:
- AI personality and tone
- Response formatting
- Business rules and guidelines

### Data Sources
Add new data by:
1. Creating methods in `AiChatController.php`
2. Updating `gatherContextData()` method
3. The AI will automatically use new data when relevant

## 🐛 Troubleshooting

### Common Issues:

1. **"OpenAI API key not configured"**
   - Check `.env` file has `OPENAI_API_KEY`
   - Restart Laravel after adding the key

2. **Chatbot not appearing**
   - Check browser console for errors
   - Verify component is imported in `App.vue`

3. **API errors**
   - Check Laravel logs: `tail -f storage/logs/laravel.log`
   - Verify OpenAI API key is valid

## 💰 Cost Management

- OpenAI charges per API call and token usage
- Monitor usage in OpenAI dashboard
- Consider rate limiting for production
- Set up billing alerts

## 📈 Performance

- Data queries are limited to prevent performance issues
- Recent data is prioritized
- Consider caching for frequently requested data

## 🔮 Future Enhancements

- Conversation memory
- File upload support
- Voice input
- Multi-language support
- Advanced analytics
- External data source integration

---

**Need help?** Check the detailed setup guide in `AI_CHATBOT_SETUP.md` 