<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, sameAs, email } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    
    import { appStore } from '@/store.js'
    import UsersService from '@/service/UsersService'
    import PermissionService from '@/service/PermissionService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.users_new')
            
            const router = useRouter()
            const usersService = new UsersService()
            const permissionService = new PermissionService()
            
            return {
                v$: useVuelidate(),
                usersService,
                permissionService,
                router
            }
        },
        data() {
            return {
                saving: false,
                user: {},
                errors: [],
                permissions: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.users'), disabled : true },
                        {'label' : this.$t('menu.users_list'), route : { name : 'users'} },
                        {'label' : this.$t('users.new_user'), route : { name : 'users'}, disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            this.permissionService.list(9999, 1)
                .then(
                    (response) => {
                        this.permissions = response.data.data
                        
                        if (this.permissions.length) {
                            this.permissions.forEach((e) => {
                                if (e.is_default) {
                                    this.user.user_permission_id = e.id
                                }
                            })
                        }
                    },
                    (error) => {}
                );
        },
        methods: {
            async createUser() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    this.usersService.create(this.user)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.$t('app.success'),
                                    detail : this.$t('users.added'),
                                });
                                
                                if(hasAccess('user:update'))
                                    this.router.push({name: 'user_edit', params: { userId : response.data }})
                                else
                                    this.router.push({name: 'users'})
                            },
                            (response) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(response);
                                this.saving = false
                            }
                        )
                } else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
        },
        validations () {
            return {
                user: {
                    firstname: { required },
                    lastname: { required },
                    email: { required, email },
                    password: { required },
                    confirm_password: { required, sameAsPassword: sameAs(this.user.password) },
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
                <form v-on:submit.prevent="createUser" class="sticky-footer-form">
                    <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="firstname" v-required class="block text-900 font-medium mb-2">{{ $t('users.firstname') }}</label>
                                    <InputText id="firstname" type="text" :placeholder="$t('users.firstname')" class="w-full" :class="{'p-invalid' : v$.user.firstname.$error}" v-model="user.firstname" :disabled="saving"/>
                                    <div v-if="v$.user.firstname.$error">
                                        <small class="p-error">{{ v$.user.firstname.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="lastname" v-required class="block text-900 font-medium mb-2">{{ $t('users.lastname') }}</label>
                                    <InputText id="lastname" type="text" :placeholder="$t('users.lastname')" class="w-full" :class="{'p-invalid' : v$.user.lastname.$error}" v-model="user.lastname" :disabled="saving" />
                                    <div v-if="v$.user.lastname.$error">
                                        <p v-for="error of v$.user.lastname.$errors" :key="error.$uid">
                                            <small class="p-error">{{ error.$message }}</small>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="email" v-required class="block text-900 font-medium mb-2">{{ $t('users.email') }}</label>
                                    <InputText id="email" type="text" :placeholder="$t('users.email')" class="w-full" :class="{'p-invalid' : v$.user.email.$error}" v-model="user.email" :disabled="saving" />
                                    <div v-if="v$.user.email.$error">
                                        <small class="p-error">{{ v$.user.email.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="phone" class="block text-900 font-medium mb-2">{{ $t('users.phone') }}</label>
                                    <InputText id="phone" type="text" :placeholder="$t('users.phone')" class="w-full" v-model="user.phone" :disabled="saving" />
                                </div>
                                
                                <div class="field col-12 mb-4">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="superuserCheck" name="superuser" value="1" v-model="user.superuser" :binary="true" :disabled="saving" />
                                        <label for="superuserCheck">{{ $t('users.superuser') }}</label>
                                    </div>
                                </div>
                                <div class="field col-12 mb-4" v-if="!user.superuser">
                                    <label for="permission_id" class="block text-900 font-medium mb-2">{{ $t('users.user_group') }}</label>
                                    <Dropdown id="permission_id" v-model="user.user_permission_id" :options="permissions" optionLabel="name" optionValue="id" style="padding: 0rem" :disabled="saving"/>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="password" v-required class="block text-900 font-medium mb-2">{{ $t('users.password') }}</label>
                                    <Password id="password" v-model="user.password" :placeholder="$t('users.password')" :feedback="false" :class="{'p-invalid' : v$.user.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="saving"></Password>
                                    <div v-if="v$.user.password.$error">
                                        <small class="p-error">{{ v$.user.password.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="confirm_password" v-required class="block text-900 font-medium mb-2">{{ $t('users.repeat_password') }}</label>
                                    <Password id="confirm_password" v-model="user.confirm_password" :placeholder="$t('users.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.user.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="saving" ></Password>
                                    <div v-if="v$.user.confirm_password.$error">
                                        <small class="p-error">{{ v$.user.confirm_password.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div class="text-right">
                            <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>