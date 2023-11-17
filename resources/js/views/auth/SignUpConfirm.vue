<script>
    import { ref } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import UserService from '@/service/UserService'
    
    import { useVuelidate } from '@vuelidate/core'
    import { required, sameAs } from '@/utils/i18n-validators'
    
    export default {
        setup() {
            const userService = new UserService()
            const router = useRouter()
            const route = useRoute()
            
            return {
                userService,
                router,
                route,
                v$: useVuelidate()
            }
        },
        data() {
            return {
                loading: false,
                firstname: '',
                lastname: '',
                password: '',
                confirm_password: '',
                phone: '',
                firm: '',
                errors: []
            }
        },
        validations () {
            return {
                firstname: { required },
                lastname: { required },
                phone: { required },
                firm: { required },
                password: { required },
                confirm_password: { required, sameAsPassword: sameAs(this.password) },
            }
        },
        methods: {
            async confirm() {
                const result = await this.v$.$validate()
                if (result) {
                    this.errors = []
                    this.loading = true
                    this.userService.registerConfirm(this.route.params.token, this.firstname, this.lastname, this.password, this.phone, this.firm)
                        .then(
                            (response) => {
                                this.loading = false
                                this.router.push({name: 'signup-confirmed'})
                            },
                            (errors) => {
                                this.getErrors(errors)
                                this.loading = false
                            }
                        );
                }
            },
            getErrors(errors) {
                for (var i in errors) {
                    errors[i].forEach((err) => {
                        this.errors.push(err);
                    });
                }
            }
        }
    }
</script>

<template>
    <div class="w-full py-6 px-5 sm:px-8">
        <div>
            <h3 class="mb-5">
                {{ $t('auth.login') }}
            </h3>
            
            <div class="mb-4">
                <div class="p-fluid">
                    <div class="formgrid grid">
                        <div class="field col">
                            <label for="firstname" class="block text-900 text-xl font-medium mb-2">{{ $t('auth.firstname') }}</label>
                            <InputText id="firstname" type="text" :placeholder="$t('auth.firstname')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.firstname.$error}" style="padding: 1rem" v-model="firstname" />
                            <div v-if="v$.firstname.$dirty">
                                <p v-for="error of v$.firstname.$errors" :key="error.$uid">
                                    <small class="p-error">{{ error.$message }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="field col">
                            <label for="lastname" class="block text-900 text-xl font-medium mb-2">{{ $t('auth.lastname') }}</label>
                            <InputText id="lastname" type="text" :placeholder="$t('auth.lastname')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.lastname.$error}" style="padding: 1rem" v-model="lastname" />
                            <div v-if="v$.lastname.$dirty">
                                <p v-for="error of v$.firstname.$errors" :key="error.$uid">
                                    <small class="p-error">{{ error.$message }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="formgrid grid">
                    <div class="field col">
                        <label for="phone" class="block text-900 text-xl font-medium mb-2">{{ $t('auth.phone') }}</label>
                        <InputText id="phone" type="text" :placeholder="$t('auth.phone')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.phone.$error}" style="padding: 1rem" v-model="phone" />
                        <div v-if="v$.phone.$dirty">
                            <p v-for="error of v$.phone.$errors" :key="error.$uid">
                                <small class="p-error">{{ error.$message }}</small>
                            </p>
                        </div>
                    </div>
                    <div class="field col">
                        <label for="firm" class="block text-900 text-xl font-medium mb-2">{{ $t('auth.firm') }}</label>
                        <InputText id="firm" type="text" :placeholder="$t('auth.firm')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.firm.$error}" style="padding: 1rem" v-model="firm" />
                        <div v-if="v$.firm.$dirty">
                            <p v-for="error of v$.firm.$errors" :key="error.$uid">
                                <small class="p-error">{{ error.$message }}</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-900 font-medium text-xl mb-2">{{ $t('auth.password') }}</label>
                <Password id="password" v-model="password" :placeholder="$t('auth.password')" :feedback="false" :class="{'p-invalid' : v$.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :inputStyle="{ padding: '1rem' }"></Password>
                <div v-if="v$.password.$dirty">
                    <p v-for="error of v$.password.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="confirm_password" class="block text-900 font-medium text-xl mb-2">{{ $t('auth.repeat_password') }}</label>
                <Password id="confirm_password" v-model="confirm_password" :placeholder="$t('auth.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :inputStyle="{ padding: '1rem' }" ></Password>
                <div v-if="v$.confirm_password.$dirty">
                    <p v-for="error of v$.confirm_password.$errors" :key="error.$uid">
                        <small class="p-error">{{ error.$message }}</small>
                    </p>
                </div>
            </div>

            <Message severity="error" :closable="false" v-if="errors.length">
                <ul class="list-unstyled">
                    <li v-for="error of errors">
                        {{ error }}
                    </li>
                </ul>
            </Message>
            
            <Button :label="$t('auth.signup_confirm')" :loading="loading" iconPos="right" @click="confirm" class="w-full p-3 text-xl text-center"></Button>
        </div>
    </div>
</template>