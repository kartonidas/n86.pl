<script>
    import { ref, reactive, computed } from 'vue'
    import { getValues } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, minValue, maxLength, maxValue  } from '@/utils/i18n-validators'
    
    import ItemService from '@/service/ItemService'
    import DictionaryService from '@/service/DictionaryService'
    import moment from 'moment'
    
    export default {
        emits: ['submit-form'],
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
            const payment_days = getValues('cyclical_fee.payment_days');
            const repeat_months = getValues('cyclical_fee.repeat_months');
            
            return {
                billTypes: [],
                payment_days,
                repeat_months
            }
        },
        props: {
            fee: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
            blockEdit: { type: Boolean, default: false }
        },
        beforeMount() {
            this.dictionaryService.listByType('bills', {size: 999, first: 0})
                .then(
                    (response) => {
                        this.billTypes = response.data.data
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async submitForm() {
                if(!this.blockEdit)
                {
                    let fee = Object.assign({}, this.fee);
                        
                    const result = await this.v.$validate()
                    if (result)
                        this.$emit('submit-form', fee)
                    else
                        this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                }
            },
            
            changeCost() {
                this.$router.push({name: 'item_cyclical_fee_costs'})
            },
            
            back() {
                this.$router.push({name: 'item_fees'})
            }
        },
        validations () {
            return {
                fee: {
                    bill_type_id: { required },
                    payment_day: { required, minValue: minValue(1), maxValueValue: maxValue(25) },
                    repeat_months: { required, minValue: minValue(1), maxValueValue: maxValue(3) },
                    cost: { required },
                    recipient_name: { maxLengthValue: maxLength(250) },
                    recipient_desciption: { maxLengthValue: maxLength(5000) },
                    recipient_bank_account: { maxLengthValue: maxLength(50) },
                    comments: { maxLengthValue: maxLength(5000) },
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
                        <Dropdown id="bill_type_id" v-model="fee.bill_type_id" :options="billTypes" :class="{'p-invalid' : v.fee.bill_type_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('items.bill_type')" class="w-full" :disabled="saving || loading"/>
                        <small>{{ $t("help.cyclical_fee_bill_type") }}</small>
                        <div v-if="v.fee.bill_type_id.$error">
                            <small class="p-error">{{ v.fee.bill_type_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4" :class="[source == 'update' ? 'md:col-6' : 'md:col-6']">
                        <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.cost') }}</label>
                        <div class="flex justify-content-between">
                            <div class="col p-0">
                                <InputNumber id="cost" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.cost')" class="w-full" :class="{'p-invalid' : v.fee.cost.$error}" v-model="fee.cost" :disabled="loading || saving || source == 'update'"/>
                            </div>
                            <div class="col-fixed pr-0" style="width:100px" v-if="source == 'update'">
                                <Button severity="secondary" :label="$t('items.change')" @click="changeCost" :disabled="blockEdit"/>
                            </div>
                        </div>
                        <small>{{ $t("help.cyclical_fee_cost") }}</small>
                        <div v-if="v.fee.cost.$error">
                            <small class="p-error">{{ v.fee.cost.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="payment_day" v-required class="block text-900 font-medium mb-2">{{ $t('items.payment_day') }}</label>
                        <Dropdown id="payment_day" v-model="fee.payment_day" :options="payment_days" optionLabel="name" :class="{'p-invalid' : v.fee.payment_day.$error}" optionValue="id" :placeholder="$t('items.select_payment_day')" class="w-full" :disabled="loading || saving"/>
                        <small>{{ $t("help.cyclical_fee_payment_day") }}</small>
                        <div v-if="v.fee.payment_day.$error">
                            <small class="p-error">{{ v.fee.payment_day.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="repeat_months" v-required class="block text-900 font-medium mb-2">{{ $t('items.repeat_months') }}</label>
                        <Dropdown id="repeat_months" v-model="fee.repeat_months" :options="repeat_months" optionLabel="name" :class="{'p-invalid' : v.fee.repeat_months.$error}" optionValue="id" :placeholder="$t('items.select_repeat_months')" class="w-full" :disabled="loading || saving"/>
                        <small>{{ $t("help.cyclical_fee_repeat_months") }}</small>
                        <div v-if="v.fee.repeat_months.$error">
                            <small class="p-error">{{ v.fee.payment_day.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="tenant_cost" class="block text-900 font-medium mb-2">{{ $t('items.tenant_cost') }}</label>
                        <div class="pt-2 pb-2">
                            <InputSwitch v-model="fee.tenant_cost" :trueValue="1" :disabled="saving || loading"/>
                        </div>
                        <small>{{ $t("help.cyclical_fee_tenant_cost") }}</small>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="recipient_name" class="block text-900 font-medium mb-2">{{ $t('items.recipient_name') }}</label>
                        <InputText id="recipient_name" type="text" :placeholder="$t('items.recipient_name')" class="w-full" :class="{'p-invalid' : v.fee.recipient_name.$error}" v-model="fee.recipient_name" :disabled="loading || saving"/>
                        <div v-if="v.fee.recipient_name.$error">
                            <small class="p-error">{{ v.fee.recipient_name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-8 mb-4">
                        <label for="recipient_desciption" class="block text-900 font-medium mb-2">{{ $t('items.recipient_desciption') }}</label>
                        <InputText id="recipient_desciption" type="text" :placeholder="$t('items.recipient_desciption')" class="w-full" :class="{'p-invalid' : v.fee.recipient_desciption.$error}" v-model="fee.recipient_desciption" :disabled="loading || saving"/>
                        <div v-if="v.fee.recipient_desciption.$error">
                            <small class="p-error">{{ v.fee.recipient_desciption.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="recipient_bank_account" class="block text-900 font-medium mb-2">{{ $t('items.recipient_bank_account') }}</label>
                        <InputText id="recipient_bank_account" type="text" :placeholder="$t('items.recipient_bank_account')" class="w-full" :class="{'p-invalid' : v.fee.recipient_bank_account.$error}" v-model="fee.recipient_bank_account" :disabled="loading || saving"/>
                        <div v-if="v.fee.recipient_bank_account.$error">
                            <small class="p-error">{{ v.fee.recipient_bank_account.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comments" class="block text-900 font-medium mb-2">{{ $t('items.comments') }}</label>
                        <Textarea id="comments" type="text" :placeholder="$t('items.comments')" rows="3" class="w-full" :class="{'p-invalid' : v.fee.comments.$error}" v-model="fee.comments" :disabled="loading || saving"/>
                        <div v-if="v.fee.comments.$error">
                            <small class="p-error">{{ v.fee.comments.$errors[0].$message }}</small>
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
                <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center" :disabled="blockEdit"></Button>
            </div>
        </div>
    </form>
</template>