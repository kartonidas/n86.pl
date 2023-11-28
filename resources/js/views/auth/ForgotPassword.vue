<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, email } from '@/utils/i18n-validators'
    import { getResponseErrors } from '@/utils/helper'
    
    import UserService from '@/service/UserService';
    
    export default {
        setup() {
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
                firmId: null,
                errors: []
            }
        },
        validations () {
            return {
                email: { required, email },
                firmId: { required: requiredIf(function() { return this.userFirms.length > 1 }) },
            }
        },
        methods: {
            async remindPassword() {
                this.errors = []
                
                const result = await this.v$.$validate()
                if (result) {
                    this.loading = true
                    if (this.formState == "email") {
                        this.userService.preLogin(this.email)
                            .then(
                                (response) => {
                                    if (response.data.length > 1) {
                                        this.loading = false
                                        this.userFirms = response.data
                                        this.v$.$reset();
                                        this.formState = "firm"
                                    } else
                                        this.forgotPassword()
                                },
                                (response) => {
                                    this.errors = getResponseErrors(response)
                                    this.loading = false
                                }
                            )
                    } else {
                        if (this.formState == "firm") {
                            this.forgotPassword()
                        }
                    }
                }
            },
            
            async forgotPassword() {
                this.userService.forgotPassword(this.email, this.firmId)
                    .then(
                        (response) => {
                            this.loading = false
                            this.router.push({name: 'forgot-password-success'})
                        },
                        (response) => {
                            this.errors = getResponseErrors(response)
                            this.loading = false
                        }
                    );
            },
            
            getCheckboxInputId(a, b) {
                return a + "-" + b
            },
            
            resetForm() {
                this.formState = "email"
                this.userFirms = []
                this.loading = false
                this.email = ''
                this.firmId = null
                this.errors = []
            }
        }
    }
</script>

<template>
    <form v-on:submit.prevent="remindPassword">
        <h5 class="mb-5 text-center">{{ $t('register.remind_password') }}</h5>
        <div class="mb-4">
            <label for="email" class="block text-900 text-lg font-medium mb-2">{{ $t('register.email') }}</label>
            <InputGroup>
                <InputText id="email" type="text" :placeholder="$t('register.email_address')" class="w-full" :class="{'p-invalid' : v$.email.$error}" v-model="email" :disabled="formState != 'email' || loading" />
                <Button icon="pi pi-times" severity="secondary" outlined @click="resetForm" v-if="formState == 'firm'"/>
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

        <Message severity="error" :closable="false" v-if="errors.length">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        
        <Button type="submit" :label="$t('register.remind_password')" :loading="loading" iconPos="right" class="w-full p-3 text-lg text-center"></Button>
    </form>
</template>
