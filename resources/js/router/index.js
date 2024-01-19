import { createRouter, createWebHistory } from 'vue-router';
import { appStore } from './../store.js';
import UserService from '@/service/UserService';
import AuthLayout from '@/layout/AuthLayout.vue';
import AppLayout from '@/layout/AppLayout.vue';
import AppItemLayout from '@/layout/AppItemLayout.vue'
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
                    meta: {permission: 'owner'},
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
                            path: '/app/items',
                            component: AppItemLayout,
                            children: [
                                {
                                    path: '/app/item/:itemId',
                                    name: 'item_show',
                                    component: () => import('@/views/app/Items/Show.vue'),
                                    meta: {permission: 'item:list'},
                                },
                                {
                                    path: '/app/item/:itemId/history',
                                    name: 'item_show_history',
                                    component: () => import('@/views/app/Items/Rental/History.vue'),
                                    meta: {permission: 'rent:list'},
                                },
                                {
                                    path: '/app/item/:itemId/reservations',
                                    name: 'item_show_reservation',
                                    component: () => import('@/views/app/Items/Rental/Reservation.vue'),
                                    meta: {permission: 'rent:list'},
                                },
                                {
                                    path: '/app/item/:itemId/reports',
                                    name: 'item_show_reports',
                                    component: () => import('@/views/app/Items/Report.vue'),
                                    meta: {permission: 'item:list'},
                                },
                                {
                                    path: '/app/item/:itemId/bills',
                                    name: 'item_bills',
                                    component: () => import('@/views/app/Items/Bills/List.vue'),
                                    meta: {permission: 'item:list'},
                                },
                                {
                                    path: '/app/item/:itemId/bill/new',
                                    name: 'item_bill_new',
                                    component: () => import('@/views/app/Items/Bills/New.vue'),
                                    meta: {permission: 'item:update'},
                                },
                                {
                                    path: '/app/item/:itemId/bill/:billId/edit',
                                    name: 'item_bill_edit',
                                    component: () => import('@/views/app/Items/Bills/Edit.vue'),
                                    meta: {permission: 'item:update'},
                                },
                                {
                                    path: '/app/item/:itemId/bill/:billId',
                                    name: 'item_bill_show',
                                    component: () => import('@/views/app/Items/Bills/Show.vue'),
                                    meta: {permission: 'item:list'},
                                },
                                {
                                    path: '/app/item/:itemId/bill/:billId/payment',
                                    name: 'item_bill_payment',
                                    component: () => import('@/views/app/Items/Bills/Payment.vue'),
                                    meta: {permission: 'item:update'},
                                },
                                {
                                    path: '/app/item/:itemId/fees',
                                    name: 'item_fees',
                                    component: () => import('@/views/app/Items/Fees/List.vue'),
                                    meta: {permission: 'item:list'},
                                },
                                {
                                    path: '/app/item/:itemId/fee/new',
                                    name: 'item_cyclical_fee_new',
                                    component: () => import('@/views/app/Items/Fees/New.vue'),
                                    meta: {permission: 'item:update'},
                                },
                                {
                                    path: '/app/item/:itemId/fee/:feeId',
                                    name: 'item_cyclical_fee_edit',
                                    component: () => import('@/views/app/Items/Fees/Edit.vue'),
                                    meta: {permission: 'item:update'},
                                },
                            ]
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
                    path: '/app/rent',
                    children: [
                        {
                            path: '/app/rent/tenant/:tenantId',
                            name: 'rent_source_tenant',
                            component: () => import('@/views/app/Rent/SourceTenant.vue'),
                            meta: {permission: 'rent:create'},
                        },
                        {
                            path: '/app/rent/item/:itemId',
                            name: 'rent_source_item',
                            component: () => import('@/views/app/Rent/SourceItem.vue'),
                            meta: {permission: 'rent:create'},
                        },
                        {
                            path: '/app/rent/new',
                            name: 'rent_source_direct',
                            component: () => import('@/views/app/Rent/SourceDirect.vue'),
                            meta: {permission: 'rent:create'},
                        },
                        {
                            path: '/app/rent/success/:rentId',
                            name: 'rent_success',
                            component: () => import('@/views/app/Rent/Success.vue'),
                            meta: {permission: 'rent:create'},
                        },
                    ]
                },
                {
                    path: '/app/rentals',
                    children: [
                        {
                            path: '/app/rentals',
                            name: 'rentals',
                            component: () => import('@/views/app/Rentals/List.vue'),
                            meta: {permission: 'rent:list'},
                        },
                        {
                            path: '/app/rental/:rentalId',
                            name: 'rental_show',
                            component: () => import('@/views/app/Rentals/Show.vue'),
                            meta: {permission: 'rent:list'},
                        },
                        {
                            path: '/app/rental/edit/:rentalId',
                            name: 'rental_edit',
                            component: () => import('@/views/app/Rentals/Edit.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/terminate',
                            name: 'rental_terminate',
                            component: () => import('@/views/app/Rentals/Terminate.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/document/new',
                            name: 'rental_document_new',
                            component: () => import('@/views/app/Rentals/Documents/New.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/document/edit/:documentId',
                            name: 'rental_document_edit',
                            component: () => import('@/views/app/Rentals/Documents/Edit.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/bill/new',
                            name: 'rental_bill_new',
                            component: () => import('@/views/app/Rentals/Bills/New.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/bill/edit/:billId',
                            name: 'rental_bill_edit',
                            component: () => import('@/views/app/Rentals/Bills/Edit.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/bill/:billId',
                            name: 'rental_bill_show',
                            component: () => import('@/views/app/Rentals/Bills/Show.vue'),
                            meta: {permission: 'rent:list'},
                        },
                        {
                            path: '/app/rental/:rentalId/bill/:billId/payment',
                            name: 'rental_bill_payment',
                            component: () => import('@/views/app/Rentals/Bills/Payment.vue'),
                            meta: {permission: 'rent:update'},
                        },
                        {
                            path: '/app/rental/:rentalId/payment',
                            name: 'rental_payment',
                            component: () => import('@/views/app/Rentals/Payment.vue'),
                            meta: {permission: 'rent:update'},
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
                        {
                            path: '/app/document/edit/:documentId',
                            name: 'document_edit',
                            component: () => import('@/views/app/Documents/Edit.vue'),
                            meta: {permission: 'document:update'},
                        },
                        {
                            path: '/app/documents/templates',
                            children: [
                                {
                                    path: '/app/documents/templates',
                                    name: 'documents_templates',
                                    component: () => import('@/views/app/Documents/Templates/List.vue'),
                                    meta: {permission: 'config:update'},
                                },
                                {
                                    path: '/app/documents/templates/new',
                                    name: 'documents_templates_new',
                                    component: () => import('@/views/app/Documents/Templates/New.vue'),
                                    meta: {permission: 'config:update'},
                                },
                                {
                                    path: '/app/documents/templates/edit/:templateId',
                                    name: 'documents_templates_edit',
                                    component: () => import('@/views/app/Documents/Templates/Edit.vue'),
                                    meta: {permission: 'config:update'},
                                },
                                {
                                    path: '/app/documents/templates/:templateId',
                                    name: 'documents_templates_show',
                                    component: () => import('@/views/app/Documents/Templates/Show.vue'),
                                    meta: {permission: 'config:update'},
                                },
                            ]
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
                    path: '/app/order',
                    children: [
                        {
                            path: '/app/order',
                            name: 'order',
                            component: () => import('@/views/app/Order/Order.vue'),
                        },
                        {
                            path: '/app/order/prolong',
                            name: 'order_prolong',
                            component: () => import('@/views/app/Order/Prolong.vue'),
                        },
                        {
                            path: '/app/order/extend',
                            name: 'order_extend',
                            component: () => import('@/views/app/Order/Extend.vue'),
                        },
                        {
                            path: '/app/order/payment/return',
                            name: 'order_payment_return',
                            component: () => import('@/views/app/Order/PaymentReturn.vue'),
                        },
                    ]
                },
                {
                    path: '/app/invoices',
                    name: 'invoices',
                    component: () => import('@/views/app/Order/Invoices.vue'),
                    meta: {permission: 'owner'},
                },
                {
                    path: '/app/user/config',
                    name: 'config',
                    component: () => import('@/views/app/Config.vue'),
                    meta: {permission: 'config:update'},
                },
                {
                    path: '/app/access-denied',
                    name: 'access_denied',
                    component: () => import('@/views/errors/AccessDenied.vue'),
                },
                {
                    path: '/app/404',
                    name: 'objectnotfound',
                    component: () => import('@/views/app/404.vue'),
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
        if (appStore().userId) {
            userService.isLogin()
                .then(
                    () => {
                        next({ name: 'dashboard' });
                    },
                    () => {
                    }
                );
        }
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