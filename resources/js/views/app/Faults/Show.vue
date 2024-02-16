<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle, hasAccess, getValueLabel } from '@/utils/helper'
    
    import Address from '@/views/app/_partials/Address.vue'
    import FaultService from '@/service/FaultService'
    import DictionaryService from '@/service/DictionaryService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.fault_show')
            
            const faultService = new FaultService()
            const dictionaryService = new DictionaryService()
            return {
                faultService,
                hasAccess,
                getValueLabel,
                dictionaryService
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                fault : {
                    status: {},
                    item: {},
                },
                faultStatuses: [],
                loadingFaultStatuses: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.faultService.get(this.$route.params.faultId)
                .then(
                    (response) => {
                        this.fault = response.data
                        this.loading = false
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
                
            this.getFaultStatuses()
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
            
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.faults'), route : { name : 'faults'} },
                    {'label' : this.$t('faults.show_fault'), disabled : true },
                ]
                    
                return items
            },
            
            editFault() {
                this.$router.push({name: 'fault_edit'})
            },
            
            back() {
                this.$router.push({name: 'faults'})
            },
            
            changeFaultStatus() {
                let data = {
                    status_id : this.fault.status_id
                }
                this.faultService.update(this.$route.params.faultId, data)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('faults.status_was_changed'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        },
                    )
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button icon="pi pi-pencil" @click="editFault" v-tooltip.left="$t('app.edit')" v-if="fault.item.mode != 'archived'"></Button>
                </div>
                
                <div class="grid mt-5">
                    <div class="col-12">
                        <div class="grid">
                            <div class="col-fixed pt-0 pb-1 align-self-center" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.priority') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ getValueLabel("faults.priorities", fault.priority) }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1 align-self-center" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.status') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <template v-if="fault.item.mode != 'archived'">
                                    <Dropdown id="status_id" v-model="fault.status_id" :loading="loadingFaultStatuses" :options="faultStatuses" optionLabel="name" optionValue="id" :placeholder="$t('faults.status')" class="w-6 max-w-20rem mr-2"/>
                                    <Button icon="pi pi-save" @click="changeFaultStatus" :disabled="loadingFaultStatuses" v-tooltip.top="$t('app.save')"></Button>
                                </template>
                                <template v-else>
                                    {{ fault.status.name }}
                                </template>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.estate') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <div :class="fault.item.mode == 'archived' ? 'archived-item' : ''">
                                    <Badge :value="getValueLabel('item_types', fault.item.type)" class="font-normal" severity="info"></Badge>
                                    <div class="mt-1">
                                        <i class="pi pi-lock pr-1" v-if="fault.item.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
                                        {{ fault.item.name }}
                                        
                                        <div>
                                            <small>
                                                <Address :object="fault.item" :newline="true" emptyChar=""/>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.description') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1" v-html="fault.description_html">
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>