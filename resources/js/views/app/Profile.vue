<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { required, requiredIf, sameAs, email } from '@/utils/i18n-validators'
    
    import UserService from '@/service/UserService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.profile')
            
            const userService = new UserService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                userService,
                v$: useVuelidate(),
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                profile: {},
                errors: [],
                changePassword: false,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.profile'), disabled : true}
                    ]
                }
            }
        },
        mounted() {
            this.userService.profile()
                .then(
                    response => {
                        this.profile = response.data
                        this.loading = false
                    },
                    response => {
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async profileUpdate() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.saving = true
                    this.userService.profileUpdate(this.profile.firstname, this.profile.lastname, this.profile.email, this.profile.phone, this.changePassword ? this.profile.password : null)
                        .then(
                            (response) => {
                                this.saving = false
                                this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('profile.updated'), life: 3000 });
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        );
                }
            },
        },
        validations () {
            return {
                profile: {
                    firstname: { required },
                    lastname: { required },
                    email: { required, email },
                    phone: { required },
                    password: { required: requiredIf(function() { return this.changePassword }) },
                    confirm_password: { required: requiredIf(function() { return this.changePassword }), sameAsPassword: sameAs(this.profile.password) },
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
                <form v-on:submit.prevent="profileUpdate">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="firstname" class="block text-900 font-medium mb-2">{{ $t('profile.firstname') }}</label>
                                    <InputText id="firstname" type="text" :placeholder="$t('profile.firstname')" class="w-full" :class="{'p-invalid' : v$.profile.firstname.$error}" v-model="profile.firstname" :disabled="loading || saving"/>
                                    <div v-if="v$.profile.firstname.$error">
                                        <small class="p-error">{{ v$.profile.firstname.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="lastname" class="block text-900 font-medium mb-2">{{ $t('profile.lastname') }}</label>
                                    <InputText id="lastname" type="text" :placeholder="$t('profile.lastname')" class="w-full" :class="{'p-invalid' : v$.profile.lastname.$error}" v-model="profile.lastname" :disabled="loading || saving"/>
                                    <div v-if="v$.profile.lastname.$error">
                                        <p v-for="error of v$.profile.lastname.$errors" :key="error.$uid">
                                            <small class="p-error">{{ error.$message }}</small>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="email" class="block text-900 font-medium mb-2">{{ $t('profile.email') }}</label>
                                    <InputText id="email" type="text" :placeholder="$t('profile.email')" class="w-full" :class="{'p-invalid' : v$.profile.email.$error}" v-model="profile.email" :disabled="loading || saving" />
                                    <div v-if="v$.profile.email.$error">
                                        <small class="p-error">{{ v$.profile.email.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4">
                                    <label for="phone" class="block text-900 font-medium mb-2">{{ $t('profile.phone') }}</label>
                                    <InputText id="phone" type="text" :placeholder="$t('profile.phone')" class="w-full" :class="{'p-invalid' : v$.profile.phone.$error}" v-model="profile.phone" :disabled="loading || saving"/>
                                    <div v-if="v$.profile.phone.$error">
                                        <small class="p-error">{{ v$.profile.phone.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="changePasswordCheck" name="changePassword" value="1" v-model="changePassword" :binary="true" :disabled="loading || saving"/>
                                        <label for="changePasswordCheck">{{ $t('profile.change_password') }}</label>
                                    </div>
                                </div>
                                
                                <div class="field col-12 sm:col-6 mb-4" v-if="changePassword">
                                    <label for="password" class="block text-900 font-medium mb-2">{{ $t('profile.password') }}</label>
                                    <Password id="password" v-model="profile.password" :placeholder="$t('profile.password')" :feedback="false" :class="{'p-invalid' : v$.profile.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading || saving"></Password>
                                    <div v-if="v$.profile.password.$error">
                                        <small class="p-error">{{ v$.profile.password.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                <div class="field col-12 sm:col-6 mb-4" v-if="changePassword">
                                    <label for="confirm_password" class="block text-900 font-medium mb-2">{{ $t('profile.repeat_password') }}</label>
                                    <Password id="confirm_password" v-model="profile.confirm_password" :placeholder="$t('profile.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.profile.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading || saving"></Password>
                                    <div v-if="v$.profile.confirm_password.$error">
                                        <small class="p-error">{{ v$.profile.confirm_password.$errors[0].$message }}</small>
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
                    
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" :disabled="loading" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>