# AI Chatbot Setup Guide

This guide will help you set up the AI-powered chatbot for your Laravel application.

## Prerequisites

1. **OpenAI API Key**: You need an OpenAI API key to use GPT-4 for the chatbot responses.
2. **Laravel Application**: This integration is designed for Laravel applications with Vue.js frontend.

## Environment Configuration

### 1. Add OpenAI API Key to .env file

Add the following line to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
```

**Important Security Notes:**
- Never commit your API key to version control
- Make sure your `.env` file is in your `.gitignore`
- Use different API keys for development and production environments

### 2. Get an OpenAI API Key

1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in to your account
3. Navigate to "API Keys" in your dashboard
4. Click "Create new secret key"
5. Copy the generated key and add it to your `.env` file

## Features

### What the Chatbot Can Do

The AI chatbot can answer questions about:

- **Orders**: Total orders, orders by client, order status, recent orders
- **Financial Data**: Income, expenses, cash-on-delivery totals, financial trends
- **Products**: Product catalog, categories, pricing information
- **Users/Agents**: User information, agent performance, role-based data
- **General Statistics**: Business overview, daily/monthly summaries

### Example Questions

Users can ask natural language questions like:
- "How many orders did client John Smith make last month?"
- "What are today's cash-on-delivery totals?"
- "Show me the total income for this month"
- "Which products are most popular?"
- "How many agents do we have?"

## Security Features

### Data Protection
- Only authenticated users can access the chatbot
- Sensitive data is filtered and sanitized before sending to OpenAI
- API responses are logged for monitoring
- Rate limiting can be implemented if needed

### Authentication
- Uses Laravel's built-in authentication middleware
- Requires verified user accounts
- CSRF protection enabled

## Technical Implementation

### Frontend (Vue.js)
- **Component**: `resources/js/components/AiChatbot.vue`
- **Features**: 
  - Floating chat button (bottom-right corner)
  - Responsive chat popup
  - Real-time message display
  - Loading animations
  - Auto-scroll to latest messages

### Backend (Laravel)
- **Controller**: `app/Http/Controllers/AiChatController.php`
- **Routes**: 
  - `POST /api/ai-chat` (with authentication)
- **Features**:
  - Intelligent data gathering based on user questions
  - Secure OpenAI API integration
  - Comprehensive error handling
  - Data sanitization and filtering

## API Endpoints

### POST /api/ai-chat

**Request:**
```json
{
  "message": "How many orders did we have today?"
}
```

**Response:**
```json
{
  "response": "Based on the data, you had 15 orders today with a total value of $2,450."
}
```

**Headers Required:**
- `Content-Type: application/json`
- `X-CSRF-TOKEN: [csrf_token]`
- Authentication (via Laravel session)

## Customization

### Modifying the Prompt

You can customize the AI prompt in `AiChatController.php` by editing the `buildPrompt()` method. This allows you to:

- Change the AI's personality or tone
- Add specific business rules or guidelines
- Modify response formatting preferences

### Adding New Data Sources

To include additional data in the chatbot responses:

1. Add new data gathering methods in `AiChatController.php`
2. Update the `gatherContextData()` method to include your new data
3. The AI will automatically use the new information when relevant

### Styling the Chatbot

The chatbot uses Tailwind CSS classes. You can customize the appearance by:

1. Modifying the classes in `AiChatbot.vue`
2. Adding custom CSS in the `<style>` section
3. Updating the color scheme to match your brand

## Troubleshooting

### Common Issues

1. **"OpenAI API key not configured"**
   - Check that `OPENAI_API_KEY` is set in your `.env` file
   - Restart your Laravel application after adding the key

2. **"Failed to get response from AI"**
   - Verify your OpenAI API key is valid
   - Check your internet connection
   - Review Laravel logs for detailed error messages

3. **Chatbot not appearing**
   - Ensure the `AiChatbot.vue` component is properly imported
   - Check browser console for JavaScript errors
   - Verify the component is included in the main App.vue template

### Debugging

Enable detailed logging by checking Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

## Performance Considerations

- The chatbot limits data queries to prevent performance issues
- Recent data is prioritized for faster responses
- Consider implementing caching for frequently requested data
- Monitor API usage to manage OpenAI costs

## Cost Management

- OpenAI charges per API call and token usage
- Monitor your usage in the OpenAI dashboard
- Consider implementing rate limiting for production use
- Set up billing alerts to avoid unexpected charges

## Support

For issues or questions about the AI chatbot integration:

1. Check the Laravel logs for error details
2. Verify your OpenAI API key and account status
3. Test with simple questions first
4. Review the data being sent to ensure it's appropriate

## Future Enhancements

Potential improvements for the chatbot:

- **Conversation Memory**: Remember previous interactions
- **File Upload**: Allow users to upload documents for analysis
- **Voice Input**: Add speech-to-text capabilities
- **Multi-language Support**: Support for multiple languages
- **Advanced Analytics**: More sophisticated data analysis
- **Integration**: Connect with external data sources 