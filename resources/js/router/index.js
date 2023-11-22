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
                {
                    path: '/app/users',
                    name: 'users',
                    component: () => import('@/views/app/Users/List.vue'),
                },
                {
                    path: '/app/user/new',
                    name: 'user_new',
                    component: () => import('@/views/app/Users/New.vue'),
                },
                {
                    path: '/app/user/:userId',
                    name: 'user_edit',
                    component: () => import('@/views/app/Users/Edit.vue'),
                },
                {
                    path: '/app/user/permissions',
                    name: 'permissions',
                    component: () => import('@/views/app/Permissions/List.vue'),
                },
                {
                    path: '/app/user/permission/new',
                    name: 'permission_new',
                    component: () => import('@/views/app/Permissions/New.vue'),
                },
                {
                    path: '/app/user/permission/:permissionId',
                    name: 'permission_edit',
                    component: () => import('@/views/app/Permissions/Edit.vue'),
                },
                {
                    path: '/app/items',
                    children: [
                        {
                            path: '/app/items',
                            name: 'items',
                            component: () => import('@/views/app/Items/List.vue'),
                        },
                        {
                            path: '/app/item/new',
                            name: 'item_new',
                            component: () => import('@/views/app/Items/New.vue'),
                        },
                        {
                            path: '/app/item/:itemId',
                            name: 'item_edit',
                            component: () => import('@/views/app/Items/Edit.vue'),
                        },
                    ]
                },
                {
                    path: '/app/dictionaries',
                    children: [
                        {
                            path: '/app/dictionary/:type(fees|bills)',
                            name: 'dictionaries',
                            component: () => import('@/views/app/Dictionaries/List.vue'),
                        },
                        {
                            path: '/app/dictionary/:type(fees|bills)/new',
                            name: 'dictionary_new',
                            component: () => import('@/views/app/Dictionaries/New.vue'),
                        },
                        {
                            path: '/app/dictionary/:type(fees|bills)/:dictionaryId',
                            name: 'dictionary_edit',
                            component: () => import('@/views/app/Dictionaries/Edit.vue'),
                        },
                    ]
                },
                {
                    path: '/app/user/config',
                    name: 'config',
                    component: () => import('@/views/app/Config.vue'),
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