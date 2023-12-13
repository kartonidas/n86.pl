<script>
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_bill_list')
            
            const itemService = new ItemService()
            
            return {
                itemService,
            }
        },
        data() {
            return {
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
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
                    items.push({'label' : this.$t('items.cyclical_fees'), disabled : true })
                }
                    
                return items
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:const" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
            </div>
        </div>
    </div>
</template>