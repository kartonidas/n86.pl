import { createRouter, createWebHistory } from 'vue-router';
import store from './../store.js';
import UserService from '@/service/UserService';
import AuthLayout from '@/layout/AuthLayout.vue';
import AppLayout from '@/layout/AppLayout.vue';
import SiteLayout from '@/layout/SiteLayout.vue';
import Home from '@/views/Home.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: SiteLayout,
            children: [
                {
                    path: '/',
                    component: Home
                },
            ]
        },
        {
            path: '/sign-in',
            component: AuthLayout,
            children: [
                {
                    path: '/sign-in',
                    name: 'signin',
                    component: () => import('@/views/auth/SignIn.vue'),
                },
            ]
        },
        {
            path: '/sign-up',
            component: AuthLayout,
            children: [
                {
                    path: '/sign-up',
                    name: 'signup',
                    component: () => import('@/views/auth/SignUp.vue'),
                },
                {
                    path: '/sign-up/success',
                    name: 'signup-success',
                    component: () => import('@/views/auth/SignUpSuccess.vue'),
                },
                {
                    path: '/sign-up/confirm/:token',
                    name: 'signup-confirm',
                    component: () => import('@/views/auth/SignUpConfirm.vue'),
                },
                {
                    path: '/sign-up/confirmed',
                    name: 'signup-confirmed',
                    component: () => import('@/views/auth/SignUpConfirmed.vue'),
                }
            ]
        },
        {
            path: '/forgot-password',
            component: AuthLayout,
            children: [
                {
                    path: '/forgot-password',
                    name: 'forgot-password',
                    component: () => import('@/views/auth/ForgotPassword.vue'),
                },
                {
                    path: '/forgot-password/success',
                    name: 'forgot-password-success',
                    component: () => import('@/views/auth/ForgotPasswordSuccess.vue'),
                },
            ]
        },
        {
            path: '/reset-password',
            component: AuthLayout,
            children: [
                {
                    path: '/reset-password',
                    name: 'reset-password',
                    component: () => import('@/views/auth/ResetPassword.vue'),
                },
                {
                    path: '/reset-password/success',
                    name: 'reset-password-success',
                    component: () => import('@/views/auth/ResetPasswordSuccess.vue'),
                },
            ]
        },
        {
            path: '/app',
            component: AppLayout,
            meta: {requiresAuth: true},
            children: [
                {
                    path: '/app',
                    name: 'dashboard',
                    component: () => import('@/views/app/Dashboard.vue'),
                },
                {
                    path: '/app/profile',
                    name: 'profile',
                    component: () => import('@/views/app/Profile.vue'),
                },
            ]
        },
        {
            path: '/logout',
            name: 'logout',
            component: () => import('@/views/auth/Logout.vue'),
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'notfound',
            component: () => import('@/views/errors/404.vue'),
        }
    ]
});

router.beforeEach((to, from, next) => {
    const userService = new UserService();
    if (to.meta.requiresAuth) {
        if (!store.state.userId)
            next({ name: 'signin' });
        else {
            userService.isLogin().then(() => next(), () => next({ name: 'signin' }));
        }
    } else {
        next();
    }
});

export default router;