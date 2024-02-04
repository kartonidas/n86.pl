<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle, hasAccess, getValueLabel } from '@/utils/helper'
    
    import Address from '@/views/app/_partials/Address.vue'
    import FaultService from '@/service/FaultService'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.fault_show')
            
            const faultService = new FaultService()
            return {
                faultService,
                hasAccess,
                getValueLabel
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
        },
        methods: {
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
                    <Button icon="pi pi-pencil" @click="editFault" v-tooltip.left="$t('app.edit')"></Button>
                </div>
                
                <div class="grid mt-5">
                    <div class="col-12">
                        <div class="grid">
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.status') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ fault.status.name }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.estate') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                
                                <Badge :value="getValueLabel('item_types', fault.item.type)" class="font-normal" severity="info"></Badge>
                                <div class="mt-1">
                                    {{ fault.item.name }}
                                            
                                    <div>
                                        <small>
                                            <Address :object="fault.item" :newline="true" emptyChar=""/>
                                        </small>
                                        
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('faults.description') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ fault.description }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>