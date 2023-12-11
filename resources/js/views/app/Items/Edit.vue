<script>
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import ItemForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { ItemForm },
        setup() {
            setMetaTitle('meta.title.items_edit')
            
            const itemService = new ItemService()
            
            return {
                itemService,
            }
        },
        data() {
            return {
                errors: [],
                item: {},
                loading: true,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} }, 
                        {'label' : this.$t('items.edit'), disabled : true },
                    ],
                },
                saving: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.itemService.get(this.$route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateItem() {
                this.saving = true
                this.errors = []
                
                this.itemService.update(this.$route.params.itemId, this.item)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.updated'), life: 3000 });
                            this.saving = false;
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(errors)
                            this.saving = false
                        }
                )
            },
            
            setErrors(errors) {
                this.errors = errors
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <h4 class="mb-5 header-border-bottom pb-2 text-color font-medium">{{ $t('items.edit') }}</h4>
                <ItemForm @submit-form="updateItem" @set-errors="setErrors" source="edit" :item="item" :saving="saving" :loading="loading" :errors="errors" />
            </div>
        </div>
    </div>
</template>