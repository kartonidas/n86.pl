<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, sameAs, email } from '@/utils/i18n-validators'
    
    import Button from 'primevue/button';
    import Checkbox from 'primevue/checkbox';
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import UserService from '@/service/UserService'
    
    export default {
        setup() {
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
                loading: false,
                profile: {},
                errors: [],
                changePassword: false,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.profile'), disabled : true}
                    ]
                }
            }
        },
        created() {
            this.userService.profile()
                .then(response => {this.profile = response.data}, () => {});
        },
        methods: {
            async profileUpdate() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.loading = true
                    this.userService.profileUpdate(this.profile.firstname, this.profile.lastname, this.profile.email, this.profile.phone)
                        .then(
                            (response) => {
                                this.loading = false
                                this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('app.profile_updated'), life: 3000 });
                            },
                            (errors) => {
                                this.getErrors(errors)
                                this.loading = false
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
        components: {
            "Breadcrumb": AppBreadcrumb,
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <div class="mb-4">
                    <div class="p-fluid">
                        <div class="formgrid grid">
                            <div class="field col-12 sm:col-6">
                                <label for="firstname" class="block text-900 text-xl font-medium mb-2">{{ $t('app.firstname') }}</label>
                                <InputText id="firstname" type="text" :placeholder="$t('app.firstname')" class="w-full" :class="{'p-invalid' : v$.profile.firstname.$error}" v-model="profile.firstname" />
                                <div v-if="v$.profile.firstname.$error">
                                    <small class="p-error">{{ v$.profile.firstname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="lastname" class="block text-900 text-xl font-medium mb-2">{{ $t('app.lastname') }}</label>
                                <InputText id="lastname" type="text" :placeholder="$t('app.lastname')" class="w-full" :class="{'p-invalid' : v$.profile.lastname.$error}" v-model="profile.lastname" />
                                <div v-if="v$.profile.lastname.$error">
                                    <p v-for="error of v$.profile.lastname.$errors" :key="error.$uid">
                                        <small class="p-error">{{ error.$message }}</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="email" class="block text-900 text-xl font-medium mb-2">{{ $t('app.email') }}</label>
                                <InputText id="email" type="text" :placeholder="$t('app.email')" class="w-full" :class="{'p-invalid' : v$.profile.email.$error}" v-model="profile.email" />
                                <div v-if="v$.profile.email.$error">
                                    <small class="p-error">{{ v$.profile.email.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="phone" class="block text-900 text-xl font-medium mb-2">{{ $t('app.phone') }}</label>
                                <InputText id="phone" type="text" :placeholder="$t('app.phone')" class="w-full" :class="{'p-invalid' : v$.profile.phone.$error}" v-model="profile.phone" />
                                <div v-if="v$.profile.phone.$error">
                                    <small class="p-error">{{ v$.profile.phone.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12">
                                <div class="field-checkbox mb-0">
                                    <Checkbox inputId="changePasswordCheck" name="changePassword" value="1" v-model="changePassword" :binary="true"/>
                                    <label for="changePasswordCheck">{{ $t('app.change_password') }}</label>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6" v-if="changePassword">
                                <label for="password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.password') }}</label>
                                <Password id="password" v-model="profile.password" :placeholder="$t('app.password')" :feedback="false" :class="{'p-invalid' : v$.profile.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full"></Password>
                                <div v-if="v$.profile.password.$error">
                                    <small class="p-error">{{ v$.profile.password.$errors[0].$message }}</small>
                                </div>
                            </div>
                            <div class="field col-12 sm:col-6" v-if="changePassword">
                                <label for="confirm_password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.repeat_password') }}</label>
                                <Password id="confirm_password" v-model="profile.confirm_password" :placeholder="$t('app.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.profile.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full"></Password>
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
                <Button :label="$t('app.save')" :loading="loading" iconPos="right" @click="profileUpdate" class="w-auto text-center"></Button>
            </div>
        </div>
    </div>
    <Toast />
</template>