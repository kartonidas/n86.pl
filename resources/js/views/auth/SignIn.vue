<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, email } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import UserService from '@/service/UserService'

    export default {
        inject : ['setAttributes', 'onLoad'],
        setup() {
            setMetaTitle('meta.title.signin')
            
            const userService = new UserService()
            const router = useRouter()
            
            return {
                userService,
                router,
                v$: useVuelidate()
            }
        },
        data() {
            return {
                formState: "email",
                userFirms: [],
                loading: false,
                email: '',
                password: '',
                firmId: null,
                errors: [],
            }
        },
        validations () {
            return {
                email: { required, email },
                password: { required: requiredIf(function() { return this.formState == "password" }) },
                firmId: { required: requiredIf(function() { return this.formState == "password" && this.userFirms.length > 1 }) },
            }
        },
        methods: {
            async login() {
                this.errors = []
                
                if (this.formState == "email") {
                    const result = await this.v$.$validate()
                    if (result) {
                        this.loading = true
                        this.userService.preLogin(this.email)
                            .then(
                                (response) => {
                                    this.formState = "password"
                                    this.userFirms = response.data
                                    this.loading = false
                                    this.v$.$reset();
                                },
                                (response) => {
                                    this.errors = getResponseErrors(response)
                                    this.loading = false
                                }
                            )
                    }
                } else {
                    if (this.formState == "password") {
                        const result = await this.v$.$validate()
                        if (result) {
                            this.loading = true
                            this.userService.login(this.email, this.password, this.firmId)
                                .then(
                                    (response) => {
                                        this.setAttributes({
                                            email : this.email,
                                            name : response.firstname + " " + response.lastname,
                                            user_id: response.id
                                        }, function(error) {});
                                        
                                        if (response.id != undefined)
                                            this.router.push({name: 'dashboard'})
                                        this.loading = false
                                    },
                                    (response) => {
                                        this.errors = getResponseErrors(response)
                                        this.loading = false
                                    }
                                );
                        }
                    }
                }
            },
            
            getCheckboxInputId(a, b) {
                return a + "-" + b
            },
            
            resetForm() {
                this.formState = "email"
                this.userFirms = []
                this.loading = false
                this.email = ''
                this.password = ''
                this.firmId = null
                this.errors = []
            }
        }
    }
</script>

<template>
    <form v-on:submit.prevent="login">
        <h5 class="mb-5 text-center">{{ $t('register.signin') }}</h5>
        <div class="mb-4">
            <label for="email" class="block text-900 text-lg font-medium mb-2">{{ $t('register.email') }}</label>
            <InputGroup>
                <InputText id="email" type="text" :placeholder="$t('register.email_address')" class="w-full" :class="{'p-invalid' : v$.email.$error}" v-model="email" :disabled="formState != 'email'" />
                <Button icon="pi pi-times" severity="secondary" outlined @click="resetForm" v-if="formState == 'password'"/>
            </InputGroup>
            <div v-if="v$.email.$dirty">
                <p v-for="error of v$.email.$errors" :key="error.$uid">
                    <small class="p-error">{{ error.$message }}</small>
                </p>
            </div>
        </div>
        
        <div class="mb-4" v-if="userFirms.length > 1">
            <label for="firm" class="block text-900 font-medium text-lg mb-2">{{ $t('register.firm') }}</label>
            <div class="field-checkbox mb-1 mt-1" v-for="userFirm in userFirms">
                <RadioButton :inputId="getCheckboxInputId('firm', userFirm.id)" :value="userFirm.id" v-model="firmId" :binary="true"/>
                <label :for="getCheckboxInputId('firm', userFirm.id)">
                    {{ userFirm.name }}
                </label>
            </div>
            <div v-if="v$.firmId.$dirty">
                <p v-for="error of v$.firmId.$errors" :key="error.$uid">
                    <small class="p-error">{{ error.$message }}</small>
                </p>
            </div>
        </div>

        <div class="mb-4" v-if="formState == 'password'">
            <label for="password" class="block text-900 font-medium text-lg mb-2">{{ $t('register.password') }}</label>
            <Password id="password" v-model="password" :placeholder="$t('register.password')" :feedback="false" :class="{'p-invalid' : v$.password.$error}" :toggleMask="true" class="w-full" inputClass="w-full"></Password>
            <div v-if="v$.password.$dirty">
                <p v-for="error of v$.password.$errors" :key="error.$uid">
                    <small class="p-error">{{ error.$message }}</small>
                </p>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="text-right mt-1">
                <router-link :to="{name: 'forgot-password'}">{{ $t('register.forgot_password') }}</router-link>
            </div>
        </div>

        <Message severity="error" :closable="false" v-if="errors.length">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        
        <Button type="submit" :label="$t('register.signin')" :loading="loading" iconPos="right" class="w-full p-3 text-lg text-center"></Button>
        
        <div class="mt-4 text-center">
            {{ $t('register.no_account_yet') }} <router-link :to="{name: 'signup'}">{{ $t('register.create_account') }}</router-link>
        </div>
    </form>
</template>
