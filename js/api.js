// JSON file paths - using absolute paths to work from any page location
const USERS_JSON_PATH = '/min/js/users.json';
const QUOTES_JSON_PATH = '/min/js/quotes.json';
const LIKES_JSON_PATH = '/min/js/likes.json';
const SAVES_JSON_PATH = '/min/js/saves.json';

// Cache for loaded data
let usersCache = null;
let quotesCache = null;
let likesCache = null;
let savesCache = null;

// Load users from JSON file
async function loadUsers() {
  if (usersCache) return usersCache;
  try {
    const res = await fetch(USERS_JSON_PATH);
    if (!res.ok) throw new Error('Failed to load users.json');
    usersCache = await res.json();
    return usersCache;
  } catch (e) {
    console.warn('Failed to load users.json, using empty array');
    usersCache = [];
    return usersCache;
  }
}

// Load quotes from JSON file
async function loadQuotes() {
  if (quotesCache) return quotesCache;
  try {
    const res = await fetch(QUOTES_JSON_PATH);
    if (!res.ok) throw new Error('Failed to load quotes.json');
    quotesCache = await res.json();
    return quotesCache;
  } catch (e) {
    console.warn('Failed to load quotes.json, using empty array');
    quotesCache = [];
    return quotesCache;
  }
}

// Load likes from JSON file
async function loadLikes() {
  if (likesCache) return likesCache;
  try {
    const res = await fetch(LIKES_JSON_PATH);
    if (!res.ok) throw new Error('Failed to load likes.json');
    likesCache = await res.json();
    return likesCache;
  } catch (e) {
    console.warn('Failed to load likes.json, using empty array');
    likesCache = [];
    return likesCache;
  }
}

// Load saves from JSON file
async function loadSaves() {
  if (savesCache) return savesCache;
  try {
    const res = await fetch(SAVES_JSON_PATH);
    if (!res.ok) throw new Error('Failed to load saves.json');
    savesCache = await res.json();
    return savesCache;
  } catch (e) {
    console.warn('Failed to load saves.json, using empty array');
    savesCache = [];
    return savesCache;
  }
}

const API = {
  async delay(ms = 500) { return new Promise(resolve => setTimeout(resolve, ms)); },

  // USER METHODS (use users.json)
  async login(email, password) {
    await this.delay();
    const users = await loadUsers();
    const user = users.find(u => u.email === email && u.password === password);
    if (!user) throw new Error('البريد الإلكتروني أو كلمة المرور غير صحيحة');
    const token = `mock_token_${user.id}_${Date.now()}`;
    const userData = { ...user };
    delete userData.password;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(userData));
    return { user: userData, token };
  },

  async signup(userData) {
    await this.delay();
    const users = await loadUsers();
    if (users.some(u => u.email === userData.email)) {
      throw new Error('البريد الإلكتروني مستخدم بالفعل');
    }
    const newUser = {
      id: String(users.length + 1),
      ...userData,
      createdAt: new Date().toISOString().split('T')[0]
    };
    users.push(newUser);
    usersCache = users;
    // Note: In a real app, this would save to server
    // For now, we just update the cache
    const token = `mock_token_${newUser.id}_${Date.now()}`;
    const userDataResponse = { ...newUser };
    delete userDataResponse.password;
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(userDataResponse));
    return { user: userDataResponse, token };
  },

  async checkEmailExists(email) {
    await this.delay(200);
    const users = await loadUsers();
    return users.some(u => u.email === email);
  },

  async getUser(userId) {
    await this.delay();
    const users = await loadUsers();
    const user = users.find(u => u.id === userId);
    if (!user) throw new Error('المستخدم غير موجود');
    const userData = { ...user };
    delete userData.password;
    return { user: userData };
  },

  async updateUser(userData) {
    await this.delay();
    const users = await loadUsers();
    const userIndex = users.findIndex(u => u.id === userData.id);
    if (userIndex === -1) throw new Error('المستخدم غير موجود');
    users[userIndex] = { ...users[userIndex], ...userData };
    usersCache = users;
    // Note: In a real app, this would save to server
    const updatedUser = { ...users[userIndex] };
    delete updatedUser.password;
    localStorage.setItem('user', JSON.stringify(updatedUser));
    return { user: updatedUser };
  },

  // QUOTES METHODS (use quotes.json)
  async getQuotes(filters = {}, page = 1, limit = 10) {
    await this.delay();
    let quotes = await loadQuotes();
    
    if (filters.userId) {
      quotes = quotes.filter(q => q.userId === filters.userId);
    }
    
    if (filters.search) {
      const searchTerm = filters.search.toLowerCase();
      quotes = quotes.filter(q =>
        q.quoteText.toLowerCase().includes(searchTerm) ||
        q.quoteAuthor.toLowerCase().includes(searchTerm) ||
        q.quoteTags.toLowerCase().includes(searchTerm)
      );
    }
    
    quotes.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedQuotes = quotes.slice(startIndex, endIndex);
    
    return {
      quotes: paginatedQuotes,
      pagination: {
        page,
        limit,
        total: quotes.length,
        totalPages: Math.ceil(quotes.length / limit)
      }
    };
  },

  async getQuote(quoteId) {
    await this.delay();
    const quotes = await loadQuotes();
    const quote = quotes.find(q => q.id === quoteId);
    if (!quote) throw new Error('الاقتباس غير موجود');
    return { quote };
  },

  async addQuote(quoteData) {
    await this.delay();
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id) throw new Error('يجب تسجيل الدخول لإضافة اقتباس');
    
    const quotes = await loadQuotes();
    const newQuote = {
      id: String(quotes.length + 1),
      ...quoteData,
      likes: 0,
      shares: 0,
      createdAt: new Date().toISOString(),
      user: {
        id: user.id,
        firstName: user.firstName,
        lastName: user.lastName
      }
    };
    
    quotes.push(newQuote);
    quotesCache = quotes;
    // Note: In a real app, this would save to server
    return { quote: newQuote };
  },

  async updateQuote(quoteId, quoteData) {
    await this.delay();
    const quotes = await loadQuotes();
    const quoteIndex = quotes.findIndex(q => q.id === quoteId);
    if (quoteIndex === -1) throw new Error('الاقتباس غير موجود');
    
    quotes[quoteIndex] = { ...quotes[quoteIndex], ...quoteData };
    quotesCache = quotes;
    // Note: In a real app, this would save to server
    return { quote: quotes[quoteIndex] };
  },

  async deleteQuote(quoteId) {
    await this.delay();
    const quotes = await loadQuotes();
    const quoteIndex = quotes.findIndex(q => q.id === quoteId);
    if (quoteIndex === -1) throw new Error('الاقتباس غير موجود');
    
    quotes.splice(quoteIndex, 1);
    quotesCache = quotes;
    // Note: In a real app, this would save to server
    return { success: true };
  },

  // LIKES METHODS (use likes.json)
  async likeQuote(quoteId) {
    await this.delay();
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id) throw new Error('يجب تسجيل الدخول للإعجاب بالاقتباس');
    
    const likes = await loadLikes();
    const likeKey = `${user.id}_${quoteId}`;
    const existingLike = likes.find(l => l === likeKey);
    
    if (!existingLike) {
      likes.push(likeKey);
      likesCache = likes;
      
      // Update quote likes count
      const quotes = await loadQuotes();
      const quote = quotes.find(q => q.id === quoteId);
      if (quote) {
        quote.likes++;
        quotesCache = quotes;
      }
    }
    
    return { success: true };
  },

  async unlikeQuote(quoteId) {
    await this.delay();
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id) throw new Error('يجب تسجيل الدخول لإلغاء الإعجاب بالاقتباس');
    
    const likes = await loadLikes();
    const likeKey = `${user.id}_${quoteId}`;
    const likeIndex = likes.findIndex(l => l === likeKey);
    
    if (likeIndex !== -1) {
      likes.splice(likeIndex, 1);
      likesCache = likes;
      
      // Update quote likes count
      const quotes = await loadQuotes();
      const quote = quotes.find(q => q.id === quoteId);
      if (quote && quote.likes > 0) {
        quote.likes--;
        quotesCache = quotes;
      }
    }
    
    return { success: true };
  },

  // SAVES METHODS (use saves.json)
  async saveQuote(quoteId) {
    await this.delay();
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id) throw new Error('يجب تسجيل الدخول لحفظ الاقتباس');
    
    const saves = await loadSaves();
    const saveKey = `${user.id}_${quoteId}`;
    const existingSave = saves.find(s => s === saveKey);
    
    if (!existingSave) {
      saves.push(saveKey);
      savesCache = saves;
    }
    
    return { success: true };
  },

  async unsaveQuote(quoteId) {
    await this.delay();
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.id) throw new Error('يجب تسجيل الدخول لإلغاء حفظ الاقتباس');
    
    const saves = await loadSaves();
    const saveKey = `${user.id}_${quoteId}`;
    const saveIndex = saves.findIndex(s => s === saveKey);
    
    if (saveIndex !== -1) {
      saves.splice(saveIndex, 1);
      savesCache = saves;
    }
    
    return { success: true };
  },

  // COMBINED METHODS
  async getUserQuotes(userId, page = 1, limit = 10) {
    return await this.getQuotes({ userId }, page, limit);
  },

  async getLikedQuotes(userId, page = 1, limit = 10) {
    await this.delay();
    const likes = await loadLikes();
    const quotes = await loadQuotes();
    
    const userLikes = likes.filter(like => like.startsWith(`${userId}_`));
    const likedQuoteIds = userLikes.map(like => like.split('_')[1]);
    const likedQuotes = quotes.filter(q => likedQuoteIds.includes(q.id));
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedQuotes = likedQuotes.slice(startIndex, endIndex);
    
    return {
      quotes: paginatedQuotes,
      pagination: {
        page,
        limit,
        total: likedQuotes.length,
        totalPages: Math.ceil(likedQuotes.length / limit)
      }
    };
  },

  async getSavedQuotes(userId, page = 1, limit = 10) {
    await this.delay();
    const saves = await loadSaves();
    const quotes = await loadQuotes();
    
    const userSaves = saves.filter(save => save.startsWith(`${userId}_`));
    const savedQuoteIds = userSaves.map(save => save.split('_')[1]);
    const savedQuotes = quotes.filter(q => savedQuoteIds.includes(q.id));
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedQuotes = savedQuotes.slice(startIndex, endIndex);
    
    return {
      quotes: paginatedQuotes,
      pagination: {
        page,
        limit,
        total: savedQuotes.length,
        totalPages: Math.ceil(savedQuotes.length / limit)
      }
    };
  }
};