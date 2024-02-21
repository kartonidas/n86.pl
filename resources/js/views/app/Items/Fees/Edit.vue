<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import TabMenu from './../_TabMenu.vue'
    import CyclicalFeeForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { CyclicalFeeForm, TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_update_cyclical_fee')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                fee : {},
                saving: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.itemService.getCyclicalFee(this.$route.params.itemId, this.$route.params.feeId)
                .then(
                    (response) => {
                        this.fee = response.data
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
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.cyclical_fees'), route : { name : 'item_fees'} })
                    items.push({'label' : this.$t('items.update_cyclical_fee'), disabled : true })
                }
                    
                return items
            },
            
            async updateCyclicalFee(fee) {
                this.saving = true
                this.errors = []
                
                this.itemService.updateCyclicalFee(this.$route.params.itemId, this.$route.params.feeId, fee)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.cyclical_fee_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="item" mark="item:cyclical|item:payment" class="text-right mb-3"/>
                <TabMenu activeIndex="fees:const" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
                <CyclicalFeeForm @submit-form="updateCyclicalFee" :fee="fee" source="update" :saving="saving" :loading="loading" :errors="errors" :blockEdit="!item.can_edit" />
            </div>
        </div>
    </div>
</template>