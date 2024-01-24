<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, maxLength } from '@/utils/i18n-validators'
    import { getValues } from '@/utils/helper'
    
    export default {
        emits: ['submit-form', 'back'],
        watch: {
            register() {
                this.toValidate = this.register
            }
        },
        data() {
            const toValidate = ref(this.register)
            const state = reactive({ 'register' : toValidate })
            const rules = computed(() => {
                const rules = {
                    register: {
                        name: { required, maxLengthValue: maxLength(100) },
                        type: { required },
                        mask: { required },
                        continuation: { required },
                    }
                }
                
                return rules
            })
            
            return {
                continuation: getValues('invoices.sale_register.continuation'),
                types: getValues('invoices.types'),
                v: useVuelidate(rules, state),
                toValidate: toValidate
            }
        },
        props: {
            register: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        methods: {
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$emit('back')
            }
        }
    };
</script>

<template>
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
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
                    <div class="field col-12 md:col-8 mb-4">
                        <label for="title" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.name') }}</label>
                        <InputText id="title" type="text" :placeholder="$t('customer_invoices.name')" class="w-full" :class="{'p-invalid' : v.register.name.$error}" v-model="register.name" :disabled="saving || loading"/>
                        <div v-if="v.register.name.$error">
                            <small class="p-error">{{ v.register.name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.document_type') }}</label>
                        <Dropdown id="type" v-model="register.type" :options="types" :class="{'p-invalid' : v.register.type.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.document_type')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.register.type.$error">
                            <small class="p-error">{{ v.register.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="formgrid grid">
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="continuation" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.continuation') }}</label>
                        <Dropdown id="continuation" v-model="register.continuation" :options="continuation" :class="{'p-invalid' : v.register.continuation.$error}" optionLabel="name" optionValue="id" :placeholder="$t('customer_invoices.continuation')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.register.continuation.$error">
                            <small class="p-error">{{ v.register.continuation.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="mask" v-required class="block text-900 font-medium mb-2">{{ $t('customer_invoices.mask') }}</label>
                        <InputText id="mask" type="text" :placeholder="$t('customer_invoices.mask')" class="w-full" :class="{'p-invalid' : v.register.mask.$error}" v-model="register.mask" :disabled="saving || loading"/>
                        <div v-if="v.register.mask.$error">
                            <small class="p-error">{{ v.register.mask.$errors[0].$message }}</small>
                        </div>
                        <small>{{ $t('customer_invoices.continuation_help') }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="source == 'new'">
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
            <div v-else>
                <div v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </div>
    </form>
</template>