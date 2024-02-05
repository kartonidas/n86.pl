<script>
    import { setMetaTitle, hasAccess } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import ItemService from '@/service/ItemService'
    import Address from '@/views/app/_partials/Address.vue'
    
    export default {
        components: { Address },
        setup() {
            setMetaTitle('meta.title.item_archive')
            
            const itemService = new ItemService()
            return {
                hasAccess,
                itemService
            }
        },
        data() {
            return {
                item: {},
                loading: true,
                displayConfirmation: false,
                saving: false,
            }
        },
        beforeMount() {
            this.getItem()
        },
        methods: {
            getItem() {
                this.itemService.get(this.$route.params.itemId)
                    .then(
                        (response) => {
                            this.item = response.data
                            this.loading = false
                            
                            if (!this.item.can_archive)
                            {
                                appStore().setError404(this.$t('items.cannot_archive'));
                                this.$router.push({name: 'objectnotfound'})
                            }
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
            
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                    items.push({'label' : this.item.name, route : { name : 'item_show', params: {itemId : this.item.id}} })
                
                items.push({'label' : this.$t('items.archive'), disabled: true })
                return items
            },
            
            back() {
                this.$router.push({name: 'item_show'})
            },
            
            openConfirmation() {
                this.displayConfirmation = true
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            confirmArchiveItem() {
                this.saving = true
                this.itemService.archive(this.item.id)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.item_archived'), life: 3000 });
                            this.$router.push({name: 'items'})
                        },
                        (errors) => {
                            this.saving = false
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.displayConfirmation = false
                        },
                    )
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1" v-if="!loading">
        <div class="col col-12">
            <div class="card text-lg text-center line-height-3">
                {{ $t('items.archive_line_1') }}
                
                <div class="mt-3 mb-5 font-semibold text-xl">
                    {{ item.name }}
                    <div class="font-medium text-lg mt-1">
                        <Address :object="item" :newline="false" emptyChar=""/>
                    </div>
                </div>
                
                <div class="text-red-500 mb-5">
                    <span class="text-2xl font-semibold">{{ $t('items.archive_danger') }}</span>
                    <div class="mb-2 mt-1">
                        {{ $t('items.archive_line_2') }}
                    </div>
                    
                    <div class="text-base">
                        {{ $t('items.archive_line_3') }}
                    </div>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="button" :label="$t('items.archive')" v-if="!loading" iconPos="right" @click="openConfirmation" icon="pi pi-inbox" severity="danger" class="w-auto text-center"></Button>
                </div>
            </div>
        </div>
    </div>
    
    <Dialog v-model:visible="displayConfirmation" :closable="false" :style="{ width: '450px' }" :modal="true">
        <div class="text-center" style="color: var(--red-600)">
            <i class="pi pi-exclamation-triangle" style="font-size: 4rem" />
            <p class="line-height-3 mt-3">
                {{ $t('items.you_intend_to_archive_item') }}<br/><span class="font-semibold">{{ item.name }}</span>
            </p>
            <p class="line-height-3 mt-3">
                {{ $t('items.irreversible_process') }}
            </p>
        </div>
        <template #footer>
            <Button :label="$t('app.cancel')" icon="pi pi-times" @click="closeConfirmation" class="p-button-secondary" autofocus />
            <Button :label="$t('items.archive')" icon="pi pi-inbox" @click="confirmArchiveItem" :disabled="saving" class="p-button-danger"  />
        </template>
    </Dialog>
</template>