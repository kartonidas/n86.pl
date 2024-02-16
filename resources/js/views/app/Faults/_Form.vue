<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, maxLength } from '@/utils/i18n-validators'
    import { getValues, getValueLabel } from '@/utils/helper'
    
    import FaultService from '@/service/FaultService'
    import DictionaryService from '@/service/DictionaryService'
    import ItemService from '@/service/ItemService'
    import Address from '@/views/app/_partials/Address.vue'
    
    import moment from 'moment'
    
    export default {
        components: { Address },
        emits: ['submit-form', 'back'],
        setup() {
            const faultService = new FaultService()
            const dictionaryService = new DictionaryService()
            const itemService = new ItemService()
            return {
                v: useVuelidate(),
                faultService,
                dictionaryService,
                itemService,
                getValueLabel
            }
        },
        data() {
            const priorities = getValues('faults.priorities');
            return {
                faultStatuses: [],
                loadingItems: false,
                items: [],
                priorities,
            }
        },
        props: {
            fault: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        beforeMount() {
            this.getFaultStatuses()
            this.getItems()
        },
        methods: {
            getFaultStatuses() {
                this.loadingFaultStatuses = true
                this.faultStatuses = []
                this.dictionaryService.listByType('fault_statuses', {size: 999, first: 0})
                    .then(
                        (response) => {
                            this.faultStatuses = response.data.data
                            this.loadingFaultStatuses = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            getItems() {
                this.loadingItems = true
                this.items = []
                this.itemService.list({size: 9999, first: 0})
                    .then(
                        (response) => {
                            this.loadingItems = false
                            if (response.data.data.length) {
                                response.data.data.forEach((i) => {
                                    this.items.push(i)
                                })
                            }
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            async submitForm() {
                let fault = Object.assign({}, this.fault);
                    
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form', fault)
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$emit('back')
            }
        },
        validations () {
            return {
                fault: {
                    status_id: { required },
                    item_id: { required },
                    priority: { required },
                    description: { required },
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
                        <label for="status_id" v-required class="block text-900 font-medium mb-2">{{ $t('faults.status') }}</label>
                        <Dropdown id="status_id" v-model="fault.status_id" :loading="loadingFaultStatuses" :options="faultStatuses" :class="{'p-invalid' : v.fault.status_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('faults.status')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.fault.status_id.$error">
                            <small class="p-error">{{ v.fault.status_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="priority" v-required class="block text-900 font-medium mb-2">{{ $t('faults.priority') }}</label>
                        <Dropdown id="priority" v-model="fault.priority" :options="priorities" :class="{'p-invalid' : v.fault.priority.$error}" optionLabel="name" optionValue="id" :placeholder="$t('faults.priority')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.fault.priority.$error">
                            <small class="p-error">{{ v.fault.priority.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="item_id" v-required class="block text-900 font-medium mb-2">{{ $t('faults.estate') }}</label>
                        <Dropdown id="item_id" v-model="fault.item_id" filter :filterFields="['name', 'street', 'city', 'zip']" :loading="loadingItems" :options="items" :class="{'p-invalid' : v.fault.item_id.$error}" optionLabel="name" optionValue="id" :placeholder="$t('faults.estate')" class="w-full" :disabled="saving || loading">
                            <template #option="slotProps">
                                <div class="">
                                    <div>
                                        {{ slotProps.option.name }}
                                    </div>
                                    <small class="font-italic text-gray-500">
                                        <Address :object="slotProps.option" :newline="false" emptyChar=""/>
                                    </small>
                                </div>
                            </template>
                        </Dropdown>
                        <div v-if="v.fault.item_id.$error">
                            <small class="p-error">{{ v.fault.item_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="description" v-required class="block text-900 font-medium mb-2">{{ $t('faults.description') }}</label>
                        <Textarea id="description" type="text" :placeholder="$t('faults.description')" rows="20" class="w-full" :class="{'p-invalid' : v.fault.description.$error}" v-model="fault.description" :disabled="loading || saving"/>
                        <div v-if="v.fault.description.$error">
                            <small class="p-error">{{ v.fault.description.$errors[0].$message }}</small>
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