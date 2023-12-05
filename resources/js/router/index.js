import { createRouter, createWebHistory } from 'vue-router';
import { appStore } from './../store.js';
import UserService from '@/service/UserService';
import AuthLayout from '@/layout/AuthLayout.vue';
import AppLayout from '@/layout/AppLayout.vue';
import SiteLayout from '@/layout/SiteLayout.vue';
import Home from '@/views/Home.vue';
import { hasAccess } from '@/utils/helper';

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
            meta: {noAuth: true},
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
            meta: {noAuth: true},
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
            meta: {noAuth: true},
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
                    path: '/app/firm-data',
                    name: 'firm_data',
                    component: () => import('@/views/app/FirmData.vue'),
                },
                {
                    path: '/app/users',
                    name: 'users',
                    component: () => import('@/views/app/Users/List.vue'),
                    meta: {permission: 'user:list'},
                },
                {
                    path: '/app/user/new',
                    name: 'user_new',
                    component: () => import('@/views/app/Users/New.vue'),
                    meta: {permission: 'user:create'},
                },
                {
                    path: '/app/user/:userId',
                    name: 'user_edit',
                    component: () => import('@/views/app/Users/Edit.vue'),
                    meta: {permission: 'user:update'},
                },
                {
                    path: '/app/user/permissions',
                    name: 'permissions',
                    component: () => import('@/views/app/Permissions/List.vue'),
                    meta: {permission: 'permission:list'},
                },
                {
                    path: '/app/user/permission/new',
                    name: 'permission_new',
                    component: () => import('@/views/app/Permissions/New.vue'),
                    meta: {permission: 'permission:create'},
                },
                {
                    path: '/app/user/permission/:permissionId',
                    name: 'permission_edit',
                    component: () => import('@/views/app/Permissions/Edit.vue'),
                    meta: {permission: 'permission:update'},
                },
                {
                    path: '/app/items',
                    children: [
                        {
                            path: '/app/items',
                            name: 'items',
                            component: () => import('@/views/app/Items/List.vue'),
                            meta: {permission: 'item:list'},
                        },
                        {
                            path: '/app/item/new',
                            name: 'item_new',
                            component: () => import('@/views/app/Items/New.vue'),
                            meta: {permission: 'item:create'},
                        },
                        {
                            path: '/app/item/new/customer/:customerId',
                            name: 'item_new_customer',
                            component: () => import('@/views/app/Items/New.vue'),
                            meta: {permission: 'item:create'},
                        },
                        {
                            path: '/app/item/edit/:itemId',
                            name: 'item_edit',
                            component: () => import('@/views/app/Items/Edit.vue'),
                            meta: {permission: 'item:update'},
                        },
                        {
                            path: '/app/item/:itemId',
                            name: 'item_show',
                            component: () => import('@/views/app/Items/Show.vue'),
                            meta: {permission: 'item:list'},
                        },
                    ]
                },
                {
                    path: '/app/customers',
                    children: [
                        {
                            path: '/app/customers',
                            name: 'customers',
                            component: () => import('@/views/app/Customers/List.vue'),
                            meta: {permission: 'customer:list'},
                        },
                        {
                            path: '/app/customer/new',
                            name: 'customer_new',
                            component: () => import('@/views/app/Customers/New.vue'),
                            meta: {permission: 'customer:create'},
                        },
                        {
                            path: '/app/customer/edit/:customerId',
                            name: 'customer_edit',
                            component: () => import('@/views/app/Customers/Edit.vue'),
                            meta: {permission: 'customer:update'},
                        },
                        {
                            path: '/app/customer/:customerId',
                            name: 'customer_show',
                            component: () => import('@/views/app/Customers/Show.vue'),
                            meta: {permission: 'customer:list'},
                        },
                    ]
                },
                {
                    path: '/app/tenants',
                    children: [
                        {
                            path: '/app/tenants',
                            name: 'tenants',
                            component: () => import('@/views/app/Tenants/List.vue'),
                            meta: {permission: 'tenant:list'},
                        },
                        {
                            path: '/app/tenant/new',
                            name: 'tenant_new',
                            component: () => import('@/views/app/Tenants/New.vue'),
                            meta: {permission: 'tenant:create'},
                        },
                        {
                            path: '/app/tenant/edit/:tenantId',
                            name: 'tenant_edit',
                            component: () => import('@/views/app/Tenants/Edit.vue'),
                            meta: {permission: 'tenant:update'},
                        },
                        {
                            path: '/app/tenant/:tenantId',
                            name: 'tenant_show',
                            component: () => import('@/views/app/Tenants/Show.vue'),
                            meta: {permission: 'tenant:list'},
                        },
                    ]
                },
                {
                    path: '/app/documents',
                    children: [
                        {
                            path: '/app/documents',
                            name: 'documents',
                            component: () => import('@/views/app/Documents/List.vue'),
                            meta: {permission: 'document:list'},
                        },
                    ]
                },
                {
                    path: '/app/faults',
                    children: [
                        {
                            path: '/app/faults',
                            name: 'faults',
                            component: () => import('@/views/app/Faults/List.vue'),
                            meta: {permission: 'fault:list'},
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
                            meta: {permission: 'dictionary:list'},
                        },
                        {
                            path: '/app/dictionary/:type(fees|bills)/new',
                            name: 'dictionary_new',
                            component: () => import('@/views/app/Dictionaries/New.vue'),
                            meta: {permission: 'dictionary:create'},
                        },
                        {
                            path: '/app/dictionary/:type(fees|bills)/:dictionaryId',
                            name: 'dictionary_edit',
                            component: () => import('@/views/app/Dictionaries/Edit.vue'),
                            meta: {permission: 'dictionary:update'},
                        },
                    ]
                },
                {
                    path: '/app/user/config',
                    name: 'config',
                    component: () => import('@/views/app/Config.vue'),
                },
                {
                    path: '/app/access-denied',
                    name: 'access_denied',
                    component: () => import('@/views/errors/AccessDenied.vue'),
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
    
    if (to.meta.noAuth) {
        if (appStore().userId)
            next({ name: 'dashboard' });
        else
            next();
    } else {
        if (to.meta.requiresAuth) {
            if (!appStore().userId)
                next({ name: 'signin' });
            else {
                if (to.meta.permission != undefined && !hasAccess(to.meta.permission))
                    next({ name: 'access_denied' });
                else
                    next();
            }
        } else {
            next();
        }
    }
});

export default router;