// Simple test API
const API = {
  async delay(ms = 500) { 
    return new Promise(resolve => setTimeout(resolve, ms)); 
  },
  
  async getQuotes() {
    await this.delay();
    return {
      quotes: [
        {
          id: '1',
          quoteText: 'اقتباس تجريبي للاختبار',
          user: { firstName: 'أحمد', lastName: 'محمود' },
          likes: 10
        }
      ]
    };
  },
  
  async login(email, password) {
    await this.delay();
    if (email === 'test@example.com' && password === '123456') {
      const user = { id: '1', firstName: 'أحمد', lastName: 'محمود', email: email };
      localStorage.setItem('token', 'mock_token');
      localStorage.setItem('user', JSON.stringify(user));
      return { user, token: 'mock_token' };
    }
    throw new Error('بيانات غير صحيحة');
  }
};

console.log('API test file loaded successfully'); 