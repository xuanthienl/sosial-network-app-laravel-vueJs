import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

// vuex
import store from "./store";

// File Views
import LoginUser from './views/user/LoginUser.vue';
import RegisterUser from './views/user/RegisterUser.vue';
import LogoutUser from './views/user/LogoutUser.vue';
import Profile from './views/user/Profile.vue';

import profilePhotos from './views/user/profile/profilePhotos.vue';
import profileIntro from './views/user/profile/profileIntro.vue';
import profileFollowers from './views/user/profile/profileFollowers.vue';
import profileFollowing from './views/user/profile/profileFollowing.vue';

import HomePage from './views/page/Home.vue';
import PostDetails from './views/page/Details.vue';

import Messages from './views/page/Messages.vue';
import Music from './views/page/Music.vue';

const routes = [
    { path: '/', name: 'home', component: HomePage, meta: { title: 'ʟ ᴏ ɴ ᴇ ʟ ʏ', requiresAuth: true } },
    { path: '/:id/details', name: 'post-details', component: PostDetails, meta: { requiresAuth: true } },

    { path: '/messages', name: 'messages', component: Messages, meta: { requiresAuth: true } },
    
    { path: '/user/:username', name: 'profile', component: Profile, meta: { requiresAuth: true },
        children: [
            { path: 'photos', name: 'photos', component: profilePhotos},
            { path: 'intro', name: 'intro', component: profileIntro},
            { path: 'followers', name: 'followers', component: profileFollowers},
            { path: 'following', name: 'following', component: profileFollowing}
        ]
    },

    { path: '/music', name: 'music', component: Music},

    { path: '/login', name: 'login', component: LoginUser, meta: { requiresVisitor: true } },
    { path: '/register', name: 'register', component: RegisterUser, meta: { requiresVisitor: true } },
    { path: '/logout', name: 'logout', component: LogoutUser},
    // { path: '/:pathMatch(.*)*', redirect: { name: 'home' } }
    //requiresAuth : Nếu chưa Login thì không được truy cập
    //requiresVisitor : Nếu đã Login thì không được truy cập
];

const router = new VueRouter({
    mode:'history',
    routes
});

//READ TITLE
router.beforeEach((to, from, next) => {
    let title = to.meta.title || 'ʟ ᴏ ɴ ᴇ ʟ ʏ';
    document.title = title;
    next();

    if (to.meta.requiresAuth) {
        if (!store.getters.loggedIn) {
            next({
                name: 'login',
            })
        } else {
            next()
        }
    } else if (to.meta.requiresVisitor) {
        if (store.getters.loggedIn) {
            next({
                name: 'home',
            })
        } else {
            next()
        }
    } else {
        next()
    }
});

export default router;