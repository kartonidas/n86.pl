<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, maxLength, helpers } from '@/utils/i18n-validators'
    import { setMetaTitle, getValues } from '@/utils/helper'
    import { pesel, nip, regon } from '@/utils/validators'
    import Countries from '@/data/countries.json'
    import ConfigService from '@/service/ConfigService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.config')
            
            const configService = new ConfigService()
            const config = ref({
                owner_type : 'person',
            })
            
            return {
                configService,
                config,
            }
        },
        watch: {
            config() {
                this.toValidate = this.config
            }
        },
        data() {
            const toValidate = ref(this.config)
            
            const state = reactive({ 'config' : toValidate })
            const rules = computed(() => {
                const rules = {
                    config: {
                        rental_numbering_mask: { required },
                        rental_numbering_continuation: { required },
                        owner_name: { required, maxLengthValue: maxLength(100) },
                        owner_type: { required },
                        owner_street: { maxLengthValue: maxLength(80) },
                        owner_house_no: { maxLengthValue: maxLength(20) },
                        owner_apartment_no: { maxLengthValue: maxLength(20) },
                        owner_city: { maxLengthValue: maxLength(120) },
                        owner_zip: { maxLengthValue: maxLength(10) },
                        owner_document_number: { maxLengthValue: maxLength(100) },
                        owner_document_extra: { maxLengthValue: maxLength(250) },
                    }
                }

                rules.config.owner_nip = state.config.owner_type == "firm" ? { nip: helpers.withMessage(this.$t('validations.nip'), nip) } : {};
                rules.config.owner_regon = state.config.owner_type == "firm" ? { regon: helpers.withMessage(this.$t('validations.regon'), regon) } : {};
                rules.config.owner_pesel = state.config.owner_type == "person" ? { pesel: helpers.withMessage(this.$t('validations.pesel'), pesel) } : {};
                
                return rules
            })
            
            return {
                mask_numbering_continuation: getValues('rental.mask_numbering_continuation'),
                loading: true,
                saving: false,
                countries: Countries[this.$i18n.locale],
                documentTypes: getValues('customer.documents'),
                types: getValues('customer_types'),
                errors: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settings'), disabled : true },
                        {'label' : this.$t('menu.configuration'), disabled : true },
                    ],
                },
                v: useVuelidate(rules, state),
                toValidate: toValidate
            }
        },
        beforeMount() {
            this.configService.get()
                .then(
                    (response) => {
                        this.loading = false;
                        this.config = Object.assign(this.config, response.data)
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    },
                )
        },
        computed: {
            labelCustomerTypeName: {
                get: function () {
                    return this.config.owner_type == 'person' ? this.$t('config.firstname_lastname') : this.$t('config.owner_name')
                },
            }
        },
        methods: {
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                {
                    this.saving = true;
                    this.configService.update(this.config)
                        .then(
                            (response) => {
                                this.saving = false;
                            },
                            (errors) => {
                                this.saving = false;
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            },
                        )
                }
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.configuration') }}</h4>
                </div>
                
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
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="mask" v-required class="block text-900 font-medium mb-2">{{ $t('config.rental_numbering_mask') }}</label>
                                    <InputText id="mask" type="text" :placeholder="$t('config.rental_numbering_mask')" class="w-full" :class="{'p-invalid' : v.config.rental_numbering_mask.$error}" v-model="config.rental_numbering_mask" :disabled="saving || loading"/>
                                    <div v-if="v.config.rental_numbering_mask.$error">
                                        <small class="p-error">{{ v.config.rental_numbering_mask.$errors[0].$message }}</small>
                                    </div>
                                    <small>{{ $t('config.rental_numbering_mask_help') }}</small>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('config.rental_numbering_continuation') }}</label>
                                    <Dropdown id="type" v-model="config.rental_numbering_continuation" :options="mask_numbering_continuation" optionLabel="name" optionValue="id" :placeholder="$t('config.rental_numbering_continuation')" class="w-full" :disabled="saving || loading"/>
                                    <div v-if="v.config.rental_numbering_continuation.$error">
                                        <small class="p-error">{{ v.config.rental_numbering_continuation.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12  md:col-4 mb-4">
                                    <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('config.owner_type') }}</label>
                                    <Dropdown id="type" v-model="config.owner_type" :options="types" optionLabel="name" optionValue="id" :placeholder="$t('config.owner_type')" class="w-full" :disabled="saving || loading"/>
                                    <div v-if="v.config.owner_type.$error">
                                        <small class="p-error">{{ v.config.owner_type.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4" :class="[config.owner_type == 'firm' ? 'md:col-8' : 'md:col-4']">
                                    <label for="name" v-required class="block text-900 font-medium mb-2">{{ labelCustomerTypeName }}</label>
                                    <InputText id="owner_name" type="text" :placeholder="$t('config.owner_name')" class="w-full" :class="{'p-invalid' : v.config.owner_name.$error}" v-model="config.owner_name" :disabled="saving || loading"/>
                                    <div v-if="v.config.owner_name.$error">
                                        <small class="p-error">{{ v.config.owner_name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                               
                                <div class="field col-12 md:col-6 mb-4" v-if="config.owner_type == 'firm'">
                                    <label for="owner_nip" class="block text-900 font-medium mb-2">{{ $t('config.owner_nip') }}</label>
                                    <InputText id="owner_nip" type="text" :placeholder="$t('config.owner_nip')" class="w-full" :class="{'p-invalid' : v.config.owner_nip.$error}" v-model="config.owner_nip" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_nip.$error">
                                        <small class="p-error">{{ v.config.owner_nip.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4" v-if="config.owner_type == 'firm'">
                                    <label for="owner_regon" class="block text-900 font-medium mb-2">{{ $t('config.owner_regon') }}</label>
                                    <InputText id="owner_regon" type="text" :placeholder="$t('config.owner_regon')" :class="{'p-invalid' : v.config.owner_regon.$error}" class="w-full" v-model="config.owner_regon" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_regon.$error">
                                        <small class="p-error">{{ v.config.owner_regon.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4" v-if="config.owner_type == 'person'">
                                    <label for="owner_pesel" class="block text-900 font-medium mb-2">{{ $t('config.owner_pesel') }}</label>
                                    <InputText id="owner_pesel" type="text" :placeholder="$t('config.owner_pesel')" class="w-full" :class="{'p-invalid' : v.config.owner_pesel.$error}" v-model="config.owner_pesel" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_pesel.$error">
                                        <small class="p-error">{{ v.config.owner_pesel.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                <div class="field col-12 md:col-3 mb-4" v-if="config.owner_type == 'person'">
                                    <label for="owner_document_type" class="block text-900 font-medium mb-2">{{ $t('config.owner_document_type') }}</label>
                                    <Dropdown id="owner_document_type" v-model="config.owner_document_type" showClear :options="documentTypes" optionLabel="name" optionValue="id" :placeholder="$t('config.owner_document_type')" class="w-full" :disabled="saving || loading"/>
                                </div>
                                <div class="field col-12 md:col-3 mb-4" v-if="config.owner_type == 'person'">
                                    <label for="owner_document_number" class="block text-900 font-medium mb-2">{{ $t('config.owner_document_number') }}</label>
                                    <InputText id="owner_document_number" type="text" :placeholder="$t('config.owner_document_number')" class="w-full" :class="{'p-invalid' : v.config.owner_document_number.$error}" v-model="config.owner_document_number" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_document_number.$error">
                                        <small class="p-error">{{ v.config.owner_document_number.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                <div class="field col-12 md:col-6 mb-4" v-if="config.owner_type == 'person'">
                                    <label for="owner_document_extra" class="block text-900 font-medium mb-2">{{ $t('config.owner_document_extra') }}</label>
                                    <InputText id="owner_document_extra" type="text" :placeholder="$t('config.owner_document_extra')" class="w-full" :class="{'p-invalid' : v.config.owner_document_extra.$error}" v-model="config.owner_document_extra" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_document_extra.$error">
                                        <small class="p-error">{{ v.config.owner_document_extra.$errors[0].$message }}</small>
                                    </div>
                                    <small>{{ $t("config.owner_document_extra_help") }}</small>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="owner_street" class="block text-900 font-medium mb-2">{{ $t('config.owner_street') }}</label>
                                    <InputText id="owner_street" type="text" :placeholder="$t('config.owner_street')" class="w-full" :class="{'p-invalid' : v.config.owner_street.$error}" v-model="config.owner_street" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_street.$error">
                                        <small class="p-error">{{ v.config.owner_street.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="owner_house_no" class="block text-900 font-medium mb-2">{{ $t('config.owner_house_no') }}</label>
                                    <InputText id="owner_house_no" type="text" :placeholder="$t('config.owner_house_no')" class="w-full" :class="{'p-invalid' : v.config.owner_house_no.$error}" v-model="config.owner_house_no" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_house_no.$error">
                                        <small class="p-error">{{ v.config.owner_house_no.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="owner_apartment_no" class="block text-900 font-medium mb-2">{{ $t('config.owner_apartment_no') }}</label>
                                    <InputText id="owner_apartment_no" type="text" :placeholder="$t('config.owner_apartment_no')" class="w-full" :class="{'p-invalid' : v.config.owner_apartment_no.$error}" v-model="config.owner_apartment_no" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_apartment_no.$error">
                                        <small class="p-error">{{ v.config.owner_apartment_no.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 sm:col-12 mb-4">
                                    <label for="owner_country" class="block text-900 font-medium mb-2">{{ $t('config.owner_country') }}</label>
                                    <Dropdown id="owner_country" v-model="config.owner_country" filter :options="countries" optionLabel="name" optionValue="code" :placeholder="$t('config.select_country')" class="w-full" :disabled="saving || loading"/>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-4 mb-4">
                                    <label for="owner_zip" class="block text-900 font-medium mb-2">{{ $t('config.owner_zip') }}</label>
                                    <InputText id="owner_zip" type="text" :placeholder="$t('config.owner_zip')" class="w-full" :class="{'p-invalid' : v.config.owner_zip.$error}" v-model="config.owner_zip" :disabled="saving || loading" />
                                    <div v-if="v.config.owner_zip.$error">
                                        <small class="p-error">{{ v.config.owner_zip.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-5 sm:col-8 mb-4">
                                    <label for="owner_city" class="block text-900 font-medium mb-2">{{ $t('config.owner_city') }}</label>
                                    <InputText id="owner_city" type="text" :placeholder="$t('config.owner_city')" class="w-full" :class="{'p-invalid' : v.config.owner_city.$error}" v-model="config.owner_city" :disabled="saving || loading"/>
                                    <div v-if="v.config.owner_city.$error">
                                        <small class="p-error">{{ v.config.owner_city.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="flex justify-content-end">
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>