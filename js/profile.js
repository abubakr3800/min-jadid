/**
 * profile.js - ملف إدارة الملف الشخصي لمنصة Min Jadeed
 * 
 * هذا الملف يحتوي على وظائف إدارة الملف الشخصي للمستخدم
 * مثل عرض وتعديل بيانات المستخدم وحذف المقالات
 */

// تهيئة وظائف إدارة الملف الشخصي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة وظائف إدارة الملف الشخصي
    Profile.init();
});

/**
 * كائن Profile الرئيسي
 */
const Profile = {
    /**
     * تهيئة وظائف إدارة الملف الشخصي
     */
    init() {
        // التحقق من نوع الصفحة الحالية
        const currentPage = window.location.pathname.split('/').pop();
        
        if (currentPage === 'profile.html') {
            // صفحة الملف الشخصي
            this.initProfilePage();
        }
    },
    
    /**
     * تهيئة صفحة الملف الشخصي
     */
    initProfilePage() {
        // التحقق من حالة تسجيل الدخول
        UI.checkAuth(true);
        
        // الحصول على معرف المستخدم من عنوان URL أو من المستخدم الحالي
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('id') || UI.getCurrentUser()?.id;
        
        if (!userId) {
            UI.showNotification('لم يتم العثور على معرف المستخدم', 'error');
            return;
        }
        
        // تحميل بيانات المستخدم
        this.loadUserProfile(userId);
        
        // تهيئة التبويبات
        this.initTabs();
        
        // تهيئة نموذج تعديل الملف الشخصي
        this.initEditProfileForm(userId);
        
        // تهيئة وظائف حذف المقالات
        this.initDeleteArticleFunctionality();
        
        // تحميل مقالات المستخدم
        this.loadUserArticles(userId);
    },
    
    /**
     * تحميل بيانات المستخدم
     * @param {string} userId - معرف المستخدم
     */
    async loadUserProfile(userId) {
        try {
            // الحصول على بيانات المستخدم
            const response = await API.getUser(userId);
            const user = response.user;
            
            if (!user) {
                throw new Error('لم يتم العثور على المستخدم');
            }
            
            // تحديث بيانات المستخدم في الصفحة
            document.getElementById('profileName').textContent = `${user.firstName} ${user.lastName}`;
            
            if (user.bio) {
                document.getElementById('profileBio').textContent = user.bio;
            } else {
                document.getElementById('profileBio').textContent = 'لا يوجد نبذة شخصية';
            }
            
            if (user.linkedinUrl) {
                const linkedinLink = document.getElementById('profileLinkedin');
                linkedinLink.href = user.linkedinUrl;
                linkedinLink.classList.remove('hidden');
            }
            
            // تنسيق تاريخ الانضمام
            const joinDate = document.getElementById('joinDate');
            if (joinDate && user.joinDate) {
                const date = new Date(user.joinDate);
                const options = { year: 'numeric', month: 'long' };
                joinDate.textContent = date.toLocaleDateString('ar-EG', options);
            }
            
            // التحقق مما إذا كان المستخدم الحالي هو صاحب الملف الشخصي
            const currentUser = UI.getCurrentUser();
            const isOwner = currentUser && currentUser.id === userId;
            
            // إظهار أو إخفاء زر تعديل الملف الشخصي
            const editProfileBtn = document.getElementById('editProfileBtn');
            if (editProfileBtn) {
                if (isOwner) {
                    editProfileBtn.classList.remove('hidden');
                } else {
                    editProfileBtn.classList.add('hidden');
                }
            }
            
            // تحديث حقول نموذج تعديل الملف الشخصي
            if (isOwner) {
                const editFirstName = document.getElementById('editFirstName');
                const editLastName = document.getElementById('editLastName');
                const editBio = document.getElementById('editBio');
                const editLinkedin = document.getElementById('editLinkedin');
                
                if (editFirstName) editFirstName.value = user.firstName;
                if (editLastName) editLastName.value = user.lastName;
                if (editBio) editBio.value = user.bio || '';
                if (editLinkedin) editLinkedin.value = user.linkedinUrl || '';
            }
        } catch (error) {
            console.error('Error loading user profile:', error);
            UI.showNotification(error.message || 'فشل تحميل بيانات المستخدم', 'error');
        }
    },
    
    /**
     * تحميل مقالات المستخدم
     * @param {string} userId - معرف المستخدم
     */
    async loadUserArticles(userId) {
        try {
            const response = await API.getPosts({ userId: userId });
            const posts = response.posts || [];
            
            const userQuotesContainer = document.getElementById('userQuotes');
            const emptyQuotes = document.getElementById('emptyQuotes');
            
            if (posts.length === 0) {
                userQuotesContainer.style.display = 'none';
                emptyQuotes.style.display = 'block';
                return;
            }
            
            userQuotesContainer.style.display = 'grid';
            emptyQuotes.style.display = 'none';
            
            // تحديث إحصائيات المستخدم
            document.getElementById('quotesCount').textContent = posts.length;
            
            // عرض المقالات
            userQuotesContainer.innerHTML = posts.map(post => this.createArticleCard(post)).join('');
            
            // إضافة مستمعي الأحداث لأزرار الحذف
            this.addDeleteEventListeners();
            
        } catch (error) {
            console.error('Error loading user articles:', error);
            UI.showNotification('فشل تحميل المقالات', 'error');
        }
    },
    
    /**
     * إنشاء بطاقة مقال
     * @param {Object} post - بيانات المقال
     * @returns {string} HTML للبطاقة
     */
    createArticleCard(post) {
        const currentUser = UI.getCurrentUser();
        const isOwner = currentUser && currentUser.id === post.userId;
        
        const deleteButton = isOwner ? `
            <button class="btn-icon delete-btn" data-post-id="${post.id}" title="حذف المقال">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';
        
        const editButton = isOwner ? `
            <button class="btn-icon" onclick="window.location.href='edit-post.php?id=${post.id}'" title="تعديل المقال">
                <i class="fas fa-edit"></i>
            </button>
        ` : '';
        
        const createdAt = new Date(post.createdAt).toLocaleDateString('ar-EG', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        return `
            <div class="quote-card" data-post-id="${post.id}">
                <div class="quote-card-actions">
                    ${editButton}
                    ${deleteButton}
                </div>
                <div class="quote-card-header">
                    <img src="../img/avatar-default.jpg" alt="صورة المستخدم" class="quote-card-avatar">
                    <div>
                        <h4 class="quote-card-author">${post.user?.firstName || 'مستخدم'} ${post.user?.lastName || ''}</h4>
                        <p class="text-medium font-sm">${createdAt}</p>
                    </div>
                </div>
                <p class="card-text">${post.title}</p>
                <div class="quote-card-meta">
                    <div class="flex gap-sm items-center">
                        <button class="btn-icon" aria-label="إعجاب">
                            <i class="fas fa-heart ${post.liked ? 'text-primary' : ''}"></i>
                        </button>
                        <span>${post.likes || 0}</span>
                    </div>
                    <div class="flex gap-sm items-center">
                        <button class="btn-icon" aria-label="مشاركة">
                            <i class="far fa-share-square"></i>
                        </button>
                        <span>${post.views || 0}</span>
                    </div>
                </div>
            </div>
        `;
    },
    
    /**
     * إضافة مستمعي الأحداث لأزرار الحذف
     */
    addDeleteEventListeners() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const postId = button.getAttribute('data-post-id');
                this.showDeleteConfirmation(postId);
            });
        });
    },
    
    /**
     * عرض نافذة تأكيد الحذف
     * @param {string} postId - معرف المقال
     */
    showDeleteConfirmation(postId) {
        // تخزين معرف المقال في النافذة المنبثقة
        document.getElementById('deleteConfirmModal').setAttribute('data-post-id', postId);
        
        // فتح النافذة المنبثقة
        UI.openModal('deleteConfirmModal');
    },
    
    /**
     * تهيئة وظائف حذف المقالات
     */
    initDeleteArticleFunctionality() {
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const closeDeleteModal = document.getElementById('closeDeleteConfirmModal');
        
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', async () => {
                const modal = document.getElementById('deleteConfirmModal');
                const postId = modal.getAttribute('data-post-id');
                
                if (postId) {
                    await this.deleteArticle(postId);
                }
            });
        }
        
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => {
                UI.closeModal('deleteConfirmModal');
            });
        }
        
        if (closeDeleteModal) {
            closeDeleteModal.addEventListener('click', () => {
                UI.closeModal('deleteConfirmModal');
            });
        }
    },
    
    /**
     * حذف مقال
     * @param {string} postId - معرف المقال
     */
    async deleteArticle(postId) {
        const confirmBtn = document.getElementById('confirmDelete');
        const btnText = confirmBtn.querySelector('.btn-text');
        const loading = confirmBtn.querySelector('.loading');
        
        // إظهار حالة التحميل
        btnText.classList.add('hidden');
        loading.classList.remove('hidden');
        
        try {
            const response = await fetch('../api/posts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    postId: postId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                UI.showNotification('تم حذف المقال بنجاح', 'success');
                
                // إغلاق النافذة المنبثقة
                UI.closeModal('deleteConfirmModal');
                
                // إزالة البطاقة من الصفحة
                const articleCard = document.querySelector(`[data-post-id="${postId}"]`);
                if (articleCard) {
                    articleCard.remove();
                }
                
                // تحديث عدد المقالات
                const currentCount = parseInt(document.getElementById('quotesCount').textContent);
                document.getElementById('quotesCount').textContent = currentCount - 1;
                
                // التحقق من وجود مقالات أخرى
                const remainingArticles = document.querySelectorAll('.quote-card');
                if (remainingArticles.length === 0) {
                    document.getElementById('userQuotes').style.display = 'none';
                    document.getElementById('emptyQuotes').style.display = 'block';
                }
                
            } else {
                throw new Error(result.error || 'فشل حذف المقال');
            }
            
        } catch (error) {
            console.error('Error deleting article:', error);
            UI.showNotification(error.message || 'فشل حذف المقال', 'error');
        } finally {
            // إخفاء حالة التحميل
            btnText.classList.remove('hidden');
            loading.classList.add('hidden');
        }
    },
    
    /**
     * تهيئة التبويبات
     */
    initTabs() {
        const tabs = document.querySelectorAll('.tab');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                
                // إزالة الفئة النشطة من جميع التبويبات
                tabs.forEach(t => t.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                
                // إضافة الفئة النشطة للتبويب المحدد
                tab.classList.add('active');
                
                // إظهار المحتوى المحدد
                const targetId = tab.getAttribute('data-tab');
                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    },
    
    /**
     * تهيئة نموذج تعديل الملف الشخصي
     * @param {string} userId - معرف المستخدم
     */
    initEditProfileForm(userId) {
        const editProfileBtn = document.getElementById('editProfileBtn');
        const editProfileForm = document.getElementById('editProfileForm');
        const saveProfileChanges = document.getElementById('saveProfileChanges');
        const cancelEditProfile = document.getElementById('cancelEditProfile');
        const closeEditProfileModal = document.getElementById('closeEditProfileModal');
        
        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', () => {
                UI.openModal('editProfileModal');
            });
        }
        
        if (cancelEditProfile) {
            cancelEditProfile.addEventListener('click', () => {
                UI.closeModal('editProfileModal');
            });
        }
        
        if (closeEditProfileModal) {
            closeEditProfileModal.addEventListener('click', () => {
                UI.closeModal('editProfileModal');
            });
        }
        
        if (saveProfileChanges) {
            saveProfileChanges.addEventListener('click', async () => {
                // الحصول على البيانات المحدثة
                const firstName = document.getElementById('editFirstName').value.trim();
                const lastName = document.getElementById('editLastName').value.trim();
                const bio = document.getElementById('editBio').value.trim();
                const linkedinUrl = document.getElementById('editLinkedin').value.trim();
                
                // التحقق من صحة البيانات
                if (!firstName || !lastName) {
                    UI.showNotification('الاسم الأول والأخير مطلوبان', 'error');
                    return;
                }
                
                // التحقق من صحة رابط LinkedIn
                if (linkedinUrl && !this.isValidLinkedinUrl(linkedinUrl)) {
                    UI.showNotification('رابط LinkedIn غير صالح', 'error');
                    return;
                }
                
                // إظهار حالة التحميل
                const btnText = saveProfileChanges.querySelector('.btn-text');
                const loading = saveProfileChanges.querySelector('.loading');
                
                btnText.classList.add('hidden');
                loading.classList.remove('hidden');
                
                try {
                    // تحديث بيانات المستخدم
                    const updatedUserData = {
                        id: userId,
                        firstName,
                        lastName,
                        bio,
                        linkedinUrl
                    };
                    
                    const response = await API.updateUser(updatedUserData);
                    
                    // تحديث بيانات المستخدم المخزنة محليًا
                    const currentUser = UI.getCurrentUser();
                    if (currentUser && currentUser.id === userId) {
                        currentUser.firstName = firstName;
                        currentUser.lastName = lastName;
                        currentUser.bio = bio;
                        currentUser.linkedinUrl = linkedinUrl;
                        
                        localStorage.setItem('currentUser', JSON.stringify(currentUser));
                    }
                    
                    // عرض رسالة نجاح
                    UI.showNotification('تم تحديث الملف الشخصي بنجاح', 'success');
                    
                    // إغلاق النافذة المنبثقة
                    UI.closeModal('editProfileModal');
                    
                    // تحديث الصفحة لعرض البيانات المحدثة
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    // عرض رسالة الخطأ
                    UI.showNotification(error.message || 'فشل تحديث الملف الشخصي. يرجى المحاولة مرة أخرى.', 'error');
                    
                    // إظهار رسالة الخطأ في النموذج
                    const errorElement = document.getElementById('editProfileError');
                    if (errorElement) {
                        errorElement.textContent = error.message || 'فشل تحديث الملف الشخصي. يرجى المحاولة مرة أخرى.';
                        errorElement.classList.remove('hidden');
                    }
                } finally {
                    // إخفاء حالة التحميل
                    btnText.classList.remove('hidden');
                    loading.classList.add('hidden');
                }
            });
        }
    },
    
    /**
     * التحقق من صحة رابط LinkedIn
     * @param {string} url - رابط LinkedIn
     * @returns {boolean} - ما إذا كان الرابط صالحًا
     */
    isValidLinkedinUrl(url) {
        // التعبير النمطي للتحقق من صحة رابط LinkedIn
        const linkedinRegex = /^(https?:\/\/)?(www\.)?linkedin\.com\/in\/[\w-]+\/?$/i;
        return linkedinRegex.test(url);
    }
};

// تصدير كائن Profile للاستخدام في ملفات أخرى
window.Profile = Profile;
