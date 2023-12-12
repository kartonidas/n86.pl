<script>
    import { getValueLabel, hasAccess } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    import Header from '@/views/app/_partials/Header.vue'
    
    export default {
        components: { Address, Header },
        setup() {
            return {
                getValueLabel,
                hasAccess
            }
        },
        data() {
            return {
                items: [
                    { label: this.$t('items.base_data'), icon: 'pi pi-fw pi-building', route: { name: 'item_show' }, index: 'base' },
                    {
                        label: this.$t('items.rent'),
                        icon: 'pi pi-fw pi-users',
                        index: 'rent',
                        items: [
                            { label: this.$t('items.reservation'), icon: 'pi pi-fw pi-list', route: { name: 'item_show_reservation' }, index: 'rent:reservation' },
                            { label: this.$t('items.history'), icon: 'pi pi-fw pi-history', route: { name: 'item_show_history' }, index: 'rent:history' },
                        ]
                    },
                    {
                        label: this.$t('items.fees'),
                        icon: 'pi pi-fw pi-wallet',
                        index: 'fees',
                        items: [
                            { label: this.$t('items.bills'), icon: 'pi pi-fw pi-money-bill', route: { name: 'item_bills' }, index: 'fees:bills' },
                            { label: this.$t('items.fixed_fees'), icon: 'pi pi-fw pi-wallet', route: { name: 'items' }, index: 'fees:const' },
                        ]
                    },
                    
                    
                    { label: this.$t('items.stats'), icon: 'pi pi-fw pi-chart-line', route: { name: 'items' }, index: 'stats' },
                ]
            }
        },
        
        props: {
            activeIndex: { type: String },
            item: { type: Object },
            showEditButton: { type : Boolean, default : true },
            showDivider: { type : Boolean, default : false }
        },
        
        methods: {
            isActive(i) {
                if (this.activeIndex != undefined)  {
                    if (i == this.activeIndex || this.activeIndex.split(":").indexOf(i) !== -1)
                        return true
                }
                return false
            }
        }
    }
</script>

<template>
    <div>
        <Header :object="item" type="item"/>
        
        <Menubar :model="items" class="mb-0 mt-5">
            <template #item="{ item, props, hasSubmenu }">
                <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                    <a :href="href" v-bind="props.action" @click="navigate" :class="[isActive(item.index) ? 'text-color-primary' : 'text-color-secondary']">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                    </a>
                </router-link>
                <a v-else  :href="item.url" :target="item.target" v-bind="props.action" :class="[isActive(item.index) ? 'text-color-primary' : 'text-color-secondary']">
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                    <span v-if="hasSubmenu" class="pi pi-fw pi-angle-down ml-2" />
                </a>
            </template>
        </Menubar>
        
        <Divider v-if="showDivider"/>
    </div>
</template>