<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, sameAs, email } from '@/utils/i18n-validators'
    
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import UsersService from '@/service/UsersService'
    
    export default {
        setup() {
            const usersService = new UsersService()
            const { t } = useI18n();
            
            return {
                t,
                v$: useVuelidate(),
                usersService,
            }
        },
        data() {
            return {
                loading: false,
                user: {},
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.users'), route : { name : 'users'} },
                        {'label' : this.t('app.new_user'), route : { name : 'users'}, disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createUser() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.usersService.create(this.user.firstname, this.user.lastname, this.user.email, this.user.phone, this.user.password).then(
                        (response) => {
                            console.log(response.data);
                        },
                        (errors) => {
                            if (errors.response.data.errors != undefined)
                                this.getErrors(errors.response.data.errors)
                            else
                                this.errors.push(errors.response.data.message)
                            this.loading = false
                        }
                    )
                }
            },
            getErrors(errors) {
                for (var i in errors) {
                    errors[i].forEach((err) => {
                        this.errors.push(err);
                    });
                }
            }
        },
        validations () {
            return {
                user: {
                    firstname: { required },
                    lastname: { required },
                    email: { required, email },
                    phone: { required },
                    password: { required },
                    confirm_password: { required, sameAsPassword: sameAs(this.user.password) },
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
                                <label for="firstname" class="block text-900 font-medium mb-2">{{ $t('auth.firstname') }}</label>
                                <InputText id="firstname" type="text" :placeholder="$t('auth.firstname')" class="w-full" :class="{'p-invalid' : v$.user.firstname.$error}" style="padding: 1rem" v-model="user.firstname" />
                                <div v-if="v$.user.firstname.$error">
                                    <small class="p-error">{{ v$.user.firstname.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="lastname" class="block text-900 font-medium mb-2">{{ $t('auth.lastname') }}</label>
                                <InputText id="lastname" type="text" :placeholder="$t('auth.lastname')" class="w-full" :class="{'p-invalid' : v$.user.lastname.$error}" style="padding: 1rem" v-model="user.lastname" />
                                <div v-if="v$.user.lastname.$error">
                                    <p v-for="error of v$.user.lastname.$errors" :key="error.$uid">
                                        <small class="p-error">{{ error.$message }}</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="email" class="block text-900 font-medium mb-2">{{ $t('auth.email') }}</label>
                                <InputText id="email" type="text" :placeholder="$t('auth.email')" class="w-full" :class="{'p-invalid' : v$.user.email.$error}" style="padding: 1rem" v-model="user.email" />
                                <div v-if="v$.user.email.$error">
                                    <small class="p-error">{{ v$.user.email.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="phone" class="block text-900 font-medium mb-2">{{ $t('auth.phone') }}</label>
                                <InputText id="phone" type="text" :placeholder="$t('auth.phone')" class="w-full" :class="{'p-invalid' : v$.user.phone.$error}" style="padding: 1rem" v-model="user.phone" />
                                <div v-if="v$.user.phone.$error">
                                    <small class="p-error">{{ v$.user.phone.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="password" class="block text-900 font-medium mb-2">{{ $t('auth.password') }}</label>
                                <Password id="password" v-model="user.password" :placeholder="$t('auth.password')" :feedback="false" :class="{'p-invalid' : v$.user.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :inputStyle="{ padding: '1rem' }"></Password>
                                <div v-if="v$.user.password.$error">
                                    <small class="p-error">{{ v$.user.password.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 sm:col-6">
                                <label for="confirm_password" class="block text-900 font-medium mb-2">{{ $t('auth.repeat_password') }}</label>
                                <Password id="confirm_password" v-model="user.confirm_password" :placeholder="$t('auth.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.user.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :inputStyle="{ padding: '1rem' }" ></Password>
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
                <Button :label="$t('app.save')" :loading="loading" iconPos="right" @click="createUser" class="w-auto text-center"></Button>
            </div>
        </div>
    </div>
</template>