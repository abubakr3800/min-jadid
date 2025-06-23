// Google Authentication using Firebase
class GoogleAuth {
    constructor() {
        this.auth = null;
        this.provider = null;
        this.init();
    }

    init() {
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyB7LgxJHT33r0v7Spjq6b2GwufGo99pYSc",
            authDomain: "min-jaded.firebaseapp.com",
            projectId: "min-jaded",
            storageBucket: "min-jaded.firebasestorage.app",
            messagingSenderId: "302914558220",
            appId: "1:302914558220:web:314e0ef56785315f229129",
            measurementId: "G-TJEBM71DEE"
        };

        // Initialize Firebase
        if (typeof firebase !== 'undefined') {
            firebase.initializeApp(firebaseConfig);
            this.auth = firebase.auth();
            this.provider = new firebase.auth.GoogleAuthProvider();
            
            // Add scopes
            this.provider.addScope('email');
            this.provider.addScope('profile');
            
            // Listen for auth state changes
            this.auth.onAuthStateChanged((user) => {
                if (user) {
                    this.handleAuthSuccess(user);
                }
            });
        } else {
            console.error('Firebase SDK not loaded');
        }
    }

    async signInWithGoogle() {
        try {
            if (!this.auth || !this.provider) {
                throw new Error('Firebase not initialized');
            }

            const result = await this.auth.signInWithPopup(this.provider);
            return result;
        } catch (error) {
            console.error('Google sign-in error:', error);
            this.handleAuthError(error);
            throw error;
        }
    }

    async signOut() {
        try {
            if (this.auth) {
                await this.auth.signOut();
                window.location.href = 'index.php';
            }
        } catch (error) {
            console.error('Sign-out error:', error);
        }
    }

    handleAuthSuccess(user) {
        // Send user data to server
        this.sendUserToServer(user);
    }

    async sendUserToServer(user) {
        try {
            const userData = {
                uid: user.uid,
                email: user.email,
                firstName: user.displayName?.split(' ')[0] || '',
                lastName: user.displayName?.split(' ').slice(1).join(' ') || '',
                photoURL: user.photoURL || '',
                provider: 'google'
            };

            const response = await fetch('api/google-auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            
            if (result.success) {
                // Redirect to homepage
                window.location.href = 'index.php';
            } else {
                console.error('Server error:', result.message);
                alert('حدث خطأ أثناء تسجيل الدخول: ' + result.message);
            }
        } catch (error) {
            console.error('Error sending user data to server:', error);
            alert('حدث خطأ أثناء تسجيل الدخول');
        }
    }

    handleAuthError(error) {
        let message = 'حدث خطأ أثناء تسجيل الدخول';
        
        switch (error.code) {
            case 'auth/popup-closed-by-user':
                message = 'تم إغلاق نافذة تسجيل الدخول';
                break;
            case 'auth/popup-blocked':
                message = 'تم حظر النافذة المنبثقة، يرجى السماح بالنوافذ المنبثقة';
                break;
            case 'auth/cancelled-popup-request':
                message = 'تم إلغاء طلب تسجيل الدخول';
                break;
            case 'auth/account-exists-with-different-credential':
                message = 'يوجد حساب بنفس البريد الإلكتروني مع طريقة تسجيل دخول مختلفة';
                break;
            default:
                message = error.message || message;
        }
        
        alert(message);
    }
}

// Initialize Google Auth when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.googleAuth = new GoogleAuth();
    
    // Add event listeners to Google sign-in buttons
    const googleButtons = document.querySelectorAll('.btn-google-signin, .btn-google-signup');
    googleButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            // Show loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري تسجيل الدخول...';
            button.disabled = true;
            
            try {
                await window.googleAuth.signInWithGoogle();
            } catch (error) {
                // Reset button state on error
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    });
}); 