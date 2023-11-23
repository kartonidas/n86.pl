<script>
    import { ref } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, sameAs } from '@/utils/i18n-validators'
    import { getResponseErrors } from '@/utils/helper'
    
    import UserService from '@/service/UserService'
    
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
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.loading = false
                            }
                        );
                }
            },
        }
    }
</script>

<template>
    <div class="w-full py-6 px-5 sm:px-8">
        <div>
            <form v-on:submit.prevent="confirm">
                <h3 class="mb-5">
                    {{ $t('app.login') }}
                </h3>
                
                <div class="mb-4">
                    <div class="p-fluid">
                        <div class="formgrid grid">
                            <div class="field col">
                                <label for="firstname" class="block text-900 text-xl font-medium mb-2">{{ $t('app.firstname') }}</label>
                                <InputText id="firstname" type="text" :placeholder="$t('app.firstname')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.firstname.$error}" v-model="firstname" :disabled="loading"/>
                                <div v-if="v$.firstname.$dirty">
                                    <p v-for="error of v$.firstname.$errors" :key="error.$uid">
                                        <small class="p-error">{{ error.$message }}</small>
                                    </p>
                                </div>
                            </div>
                            <div class="field col">
                                <label for="lastname" class="block text-900 text-xl font-medium mb-2">{{ $t('app.lastname') }}</label>
                                <InputText id="lastname" type="text" :placeholder="$t('app.lastname')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.lastname.$error}" v-model="lastname" :disabled="loading" />
                                <div v-if="v$.lastname.$dirty">
                                    <p v-for="error of v$.lastname.$errors" :key="error.$uid">
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
                            <label for="phone" class="block text-900 text-xl font-medium mb-2">{{ $t('app.phone') }}</label>
                            <InputText id="phone" type="text" :placeholder="$t('app.phone')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.phone.$error}" v-model="phone" :disabled="loading" />
                            <div v-if="v$.phone.$dirty">
                                <p v-for="error of v$.phone.$errors" :key="error.$uid">
                                    <small class="p-error">{{ error.$message }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="field col">
                            <label for="firm" class="block text-900 text-xl font-medium mb-2">{{ $t('app.firm') }}</label>
                            <InputText id="firm" type="text" :placeholder="$t('app.firm')" class="w-full md:w-20rem" :class="{'p-invalid' : v$.firm.$error}" v-model="firm" :disabled="loading" />
                            <div v-if="v$.firm.$dirty">
                                <p v-for="error of v$.firm.$errors" :key="error.$uid">
                                    <small class="p-error">{{ error.$message }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="mb-4">
                    <label for="password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.password') }}</label>
                    <Password id="password" v-model="password" :placeholder="$t('app.password')" :feedback="false" :class="{'p-invalid' : v$.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading"></Password>
                    <div v-if="v$.password.$dirty">
                        <p v-for="error of v$.password.$errors" :key="error.$uid">
                            <small class="p-error">{{ error.$message }}</small>
                        </p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="block text-900 font-medium text-xl mb-2">{{ $t('app.repeat_password') }}</label>
                    <Password id="confirm_password" v-model="confirm_password" :placeholder="$t('app.repeat_password')" :feedback="false" :class="{'p-invalid' : v$.confirm_password.$error}" :toggleMask="true" class="w-full" inputClass="w-full" :disabled="loading"></Password>
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
                
                <Button type="submit" :label="$t('app.signup_confirm')" :loading="loading" iconPos="right" class="w-full p-3 text-xl text-center"></Button>
            </form>
        </div>
    </div>
</template>