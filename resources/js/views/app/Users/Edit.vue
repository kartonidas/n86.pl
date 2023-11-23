<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, sameAs, email } from '@/utils/i18n-validators'
    import { getResponseErrors } from '@/utils/helper'
    
    import store from '@/store.js'
    import UsersService from '@/service/UsersService'
    import PermissionService from '@/service/PermissionService'
    
    export default {
        setup() {
            const usersService = new UsersService()
            const permissionService = new PermissionService()
            const { t } = useI18n();
            const route = useRoute()
            const toast = useToast();
            
            return {
                t,
                v$: useVuelidate(),
                usersService,
                permissionService,
                route,
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                user: {},
                errors: [],
                changePassword: false,
                permissions: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.users'), disabled : true },
                        {'label' : this.t('app.users_list'), route : { name : 'users'} },
                        {'label' : this.t('app.edit_user'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            if(store.getters.toastMessage) {
                let m = store.getters.toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                store.commit('setToastMessage', null);
            }
            
            this.usersService.get(this.route.params.userId)
                .then(
                    (response) => {
                        this.user = response.data
                        this.loading = false
                    },
                    (errors) => {
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
                
            this.permissionService.list(9999, 1)
                .then(
                    (response) => {
                        this.permissions = response.data.data
                    },
                    (error) => {}
                );
        },
        methods: {
            async updateUser() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true;
                    this.errors = []
                    
                    this.usersService.update(
                            this.route.params.userId,
                            this.user.firstname,
                            this.user.lastname,
                            this.user.email,
                            this.user.phone,
                            this.changePassword ? this.user.password : null,
                            this.user.superuser,
                            this.user.user_permission_id,
                        )
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('app.user_updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.saving = false;
                                this.errors = getResponseErrors(response);
                            },
                        );
                }
            },
            
            getPasswordToCompare() {
                return this.changePassword ? this.user.password : this.user.confirm_password;
            },
        },
        validations () {
            return {
                user: {
                    firstname: { required },
                    lastname: { required },
                    email: { required, email },
                    phone: { required },
                    password: { required: requiredIf(function() { return this.changePassword }) },
                    confirm_password: { required: requiredIf(function() { return this.changePassword }), sameAsRef: sameAs(this.getPasswordToCompare()) },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <form v-on:submit.prevent="updateUser">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 sm:col-6 mb-2">
                                    <label for="firstname" class="block text-900 font-medium mb-2">{{ $t('app.firstname') }}</label>
                                    <InputText id="firstname" type="text" :placeholder="$t('app.firstname')" class="w-full" :class="{'p-invalid' : v$.user.firstname.$error}" v-model="user.firstname" :disabled="loading || saving" />
                                    <div v-if="v$.user.firstname.$error">
                                        <small class="p-error">{{ v$.user.firstname.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6">
                                    <label for="lastname" class="block text-900 font-medium mb-2">{{ $t('app.lastname') }}</label>
                                    <InputText id="lastname" type="text" :placeholder="$t('app.lastname')" class="w-full" :class="{'p-invalid' : v$.user.lastname.$error}" v-model="user.lastname" :disabled="loading || saving" />
                                    <div v-if="v$.user.lastname.$error">
                                        <p v-for="error of v$.user.lastname.$errors" :key="error.$uid">
                                            <small class="p-error">{{ error.$message }}</small>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6">
                                    <label for="email" class="block text-900 font-medium mb-2">{{ $t('app.email') }}</label>
                                    <InputText id="email" type="text" :placeholder="$t('app.email')" class="w-full" :class="{'p-invalid' : v$.user.email.$error}" v-model="user.email" :disabled="loading || saving" />
                                    <div v-if="v$.user.email.$error">
                                        <small class="p-error">{{ v$.user.email.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6">
                                    <label for="phone" class="block text-900 font-medium mb-2">{{ $t('app.phone') }}</label>
                                    <InputText id="phone" type="text" :placeholder="$t('app.phone')" class="w-full" :class="{'p-invalid' : v$.user.phone.$error}" v-model="user.phone" :disabled="loading || saving" />
                                    <div v-if="v$.user.phone.$error">
                                        <small class="p-error">{{ v$.user.phone.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="superuserCheck" name="superuser" value="1" v-model="user.superuser" :binary="true" :disabled="loading || saving"/>
                                        <label for="superuserCheck">{{ $t('app.superuser') }}</label>
                                    </div>
                                </div>
                                <div class="field col-12" v-if="!user.superuser">
                                    <label for="phone" class="block text-900 font-medium mb-2">{{ $t('app.user_group') }}</label>
                                    <Dropdown v-model="user.user_permission_id" :options="permissions" optionLabel="name" optionValue="id" :disabled="loading || saving"/>
                                </div>
                                
                                <div class="field col-12">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="changePasswordCheck" name="changePassword" value="1" v-model="changePassword" :binary="true" :disabled="loading || saving"/>
                                        <label for="changePasswordCheck">{{ $t('app.change_password') }}</label>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6" v-if="changePassword">
                                    <label for="password" class="block text-900 font-medium mb-2">{{ $t('app.password') }}</label>
                                    <Password id="password" v-model="user.password" :placeholder="$t('app.password')" :feedback="false" :class="{'p-invalid' : v$.user.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading || saving"></Password>
                                    <div v-if="v$.user.password.$error">
                                        <small class="p-error">{{ v$.user.password.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6" v-if="changePassword">
                                    <label for="confirm_password" class="block text-900 font-medium mb-2">{{ $t('app.repeat_password') }}</label>
                                    <Password id="confirm_password" v-model="user.confirm_password" :placeholder="$t('app.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.user.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading || saving"></Password>
                                    <div v-if="v$.user.confirm_password.$error">
                                        <small class="p-error">{{ v$.user.confirm_password.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <Message severity="error" :closable="false" v-if="errors.length">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    
                    <div class="" v-if="loading">
                        <ProgressSpinner style="width: 25px; height: 25px"/>
                    </div>
                    
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" class="w-auto text-center"></Button>
                </form>
            </div>
        </div>
    </div>
</template>