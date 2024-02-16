<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email, helpers, maxLength } from '@/utils/i18n-validators'
    
    import ItemService from '@/service/ItemService'
    import DictionaryService from '@/service/DictionaryService'
    import moment from 'moment'
    
    export default {
        emits: ['submit-form', 'back'],
        setup() {
            const itemService = new ItemService()
            const dictionaryService = new DictionaryService()
            return {
                v: useVuelidate(),
                itemService,
                dictionaryService,
            }
        },
        data() {
            return {
                billTypes: [],
            }
        },
        props: {
            bill: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        beforeMount() {
            this.dictionaryService.listByType('bills', {size: 999, first: 0})
                .then(
                    (response) => {
                        this.billTypes = response.data.data
                        if(this.bill.bill_type_id < 0)
                            this.billTypes.push({"id" : this.bill.bill_type_id, "name" : this.bill.bill_type.name})
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async submitForm() {
                let bill = Object.assign({}, this.bill);
                if(bill.payment_date && bill.payment_date instanceof Date)
                    bill.payment_date = moment(bill.payment_date).format("YYYY-MM-DD")
                    
                if(bill.source_document_date && bill.source_document_date instanceof Date)
                    bill.source_document_date = moment(bill.source_document_date).format("YYYY-MM-DD")
                    
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form', bill)
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$emit('back')
            }
        },
        validations () {
            return {
                bill: {
                    bill_type_id: { required },
                    payment_date: { required },
                    cost: { required },
                    recipient_name: { maxLengthValue: maxLength(100) },
                    recipient_desciption: { maxLengthValue: maxLength(5000) },
                    recipient_bank_account: { maxLengthValue: maxLength(50) },
                    comments: { maxLengthValue: maxLength(5000) },
                    source_document_number: { maxLengthValue: maxLength(100) },
                }
            }
        },
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
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="bill_type_id" v-required class="block text-900 font-medium mb-2">{{ $t('items.bill_type') }}</label>
                        <Dropdown id="bill_type_id" v-model="bill.bill_type_id" :options="billTypes" :class="{'p-invalid' : v.bill.bill_type_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('items.bill_type')" class="w-full" :disabled="saving || loading || (source == 'update' && bill.bill_type_id < 0)"/>
                        <small>{{ $t("help.bill_bill_type") }}</small>
                        <div v-if="v.bill.bill_type_id.$error">
                            <small class="p-error">{{ v.bill.bill_type_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.cost') }}</label>
                        <InputNumber id="cost" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.cost')" class="w-full" :class="{'p-invalid' : v.bill.cost.$error}" v-model="bill.cost" :disabled="loading || saving"/>
                        <small>{{ $t("help.bill_cost") }}</small>
                        <div v-if="v.bill.cost.$error">
                            <small class="p-error">{{ v.bill.cost.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="payment_date" v-required class="block text-900 font-medium mb-2">{{ $t('items.payment_date') }}</label>
                        <Calendar id="payment_date" v-model="bill.payment_date" :class="{'p-invalid' : v.bill.payment_date.$error}" :placeholder="$t('items.payment_date')" showIcon :disabled="loading || saving"/>
                        <small>{{ $t("help.bill_payment_date") }}</small>
                        <div v-if="v.bill.payment_date.$error">
                            <small class="p-error">{{ v.bill.payment_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4" v-if="source == 'new'">
                        <label for="charge_current_tenant" class="block text-900 font-medium mb-2">{{ $t('items.tenant_cost') }}</label>
                        <div class="pt-2 pb-2">
                            <InputSwitch v-model="bill.charge_current_tenant" :trueValue="1" :disabled="saving || loading"/>
                        </div>
                        <small>{{ $t("help.bill_tenant_cost") }}</small>
                    </div>
                    <div class="field col-12 md:col-6 mb-4" v-else>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="recipient_name" class="block text-900 font-medium mb-2">{{ $t('items.recipient_name') }}</label>
                        <InputText id="recipient_name" type="text" :placeholder="$t('items.recipient_name')" class="w-full" :class="{'p-invalid' : v.bill.recipient_name.$error}" v-model="bill.recipient_name" :disabled="loading || saving"/>
                        <div v-if="v.bill.recipient_name.$error">
                            <small class="p-error">{{ v.bill.recipient_name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-8 mb-4">
                        <label for="recipient_desciption" class="block text-900 font-medium mb-2">{{ $t('items.recipient_desciption') }}</label>
                        <InputText id="recipient_desciption" type="text" :placeholder="$t('items.recipient_desciption')" class="w-full" :class="{'p-invalid' : v.bill.recipient_desciption.$error}" v-model="bill.recipient_desciption" :disabled="loading || saving"/>
                        <div v-if="v.bill.recipient_desciption.$error">
                            <small class="p-error">{{ v.bill.recipient_desciption.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="recipient_bank_account" class="block text-900 font-medium mb-2">{{ $t('items.recipient_bank_account') }}</label>
                        <InputText id="recipient_bank_account" type="text" :placeholder="$t('items.recipient_bank_account')" class="w-full" :class="{'p-invalid' : v.bill.recipient_bank_account.$error}" v-model="bill.recipient_bank_account" :disabled="loading || saving"/>
                        <div v-if="v.bill.recipient_bank_account.$error">
                            <small class="p-error">{{ v.bill.recipient_bank_account.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4" v-if="source == 'update' && bill.bill_type_id > 0">
                        <label for="source_document_number" class="block text-900 font-medium mb-2">{{ $t('items.source_document_number') }}</label>
                        <InputText id="source_document_number" type="text" :placeholder="$t('items.source_document_number')" class="w-full" :class="{'p-invalid' : v.bill.source_document_number.$error}" v-model="bill.source_document_number" :disabled="loading || saving"/>
                        <small>{{ $t("help.bill_source_document_number") }}</small>
                        <div v-if="v.bill.source_document_number.$error">
                            <small class="p-error">{{ v.bill.source_document_number.$errors[0].$message }}</small>
                        </div>
                    </div>
                    <div class="field col-12 md:col-6 mb-4" v-if="source == 'update' && bill.bill_type_id > 0">
                        <label for="source_document_date" class="block text-900 font-medium mb-2">{{ $t('items.source_document_date') }}</label>
                        <Calendar id="source_document_date" v-model="bill.source_document_date" :placeholder="$t('items.source_document_date')" showIcon :disabled="loading || saving"/>
                        <small>{{ $t("help.bill_source_document_date") }}</small>
                    </div>
                    
                    
                    <div class="field col-12 mb-4">
                        <label for="comments" class="block text-900 font-medium mb-2">{{ $t('items.comments') }}</label>
                        <Textarea id="comments" type="text" :placeholder="$t('items.comments')" rows="3" class="w-full" :class="{'p-invalid' : v.bill.comments.$error}" v-model="bill.comments" :disabled="loading || saving"/>
                        <div v-if="v.bill.comments.$error">
                            <small class="p-error">{{ v.bill.comments.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="source != 'new' && loading">
                <ProgressSpinner style="width: 25px; height: 25px"/>
            </div>
            
            <div class="flex justify-content-between align-items-center">
                <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </div>
    </form>
</template>