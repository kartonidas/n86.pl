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
            setMetaTitle('meta.title.items_new_cyclical_fee')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                errors: [],
                fee : {},
                saving: false
            }
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
                    items.push({'label' : this.$t('items.new_cyclical_fee'), disabled : true })
                }
                    
                return items
            },
            
            async createCyclicalFee(fee) {
                this.saving = true
                this.errors = []
                
                this.itemService.createCyclicalFee(this.$route.params.itemId, fee)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('items.cyclical_fee_added'),
                            });
                            
                            this.$router.push({name: 'item_cyclical_fee_edit', params: { itemId : this.$route.params.itemId, feeId : response.data }})
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
                <CyclicalFeeForm @submit-form="createCyclicalFee" :fee="fee" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>