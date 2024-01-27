<script>
    import { p } from '@/utils/helper'
    import OrderService from '@/service/OrderService'
    import DashboardService from '@/service/DashboardService'
    import Skeleton from 'primevue/skeleton';
    
    export default {
        components: { Skeleton },
        props: {
            location: { type: String },
            class: {
                type: String,
                default: "mt-3 mb-3"
            },
        },
        setup() {
            const orderService = new OrderService()
            return {
                orderService,
                p
            }
        },
        data() {
            return {
                activeSubscription: false,
                items: 0,
                end_date: null,
                loading: true,
            }
        },
        beforeMount() {
            this.orderService.getActiveSubscription()
                .then(
                    (response) => {
                        if(response.data.start !== undefined)
                        {
                            this.activeSubscription = true
                            this.items = response.data.items
                            this.end_date = response.data.end_date
                        }
                        this.loading = false
                    },
                    (errors) => {},
                )
        },
        methods: {
            prolong() {
                this.$router.push({name: 'order_prolong'})
            },
            extend() {
                this.$router.push({name: 'order_extend'})
            },
            order() {
                this.$router.push({name: 'order'})
            }
        }
    };
</script>

<template>
    <Skeleton style="min-height:120px" v-if="loading"></Skeleton>
    <template v-if="!loading">
        <div v-if="activeSubscription" class="bg-green-100 p-3 text-center text-sm border-round-lg" :class="class">
            {{ $t("orders.currently_package", [items, p(items, $t('orders.estate_1'), $t('orders.estate_2'), $t('orders.estate_3'))]) }}<br/>({{ end_date }})
            <div class="mt-2 mb-2">
                <Button severity="warning" size="small" class="mr-1" @click="prolong">{{ $t("orders.prolong") }}</Button>
                <Button severity="danger" size="small" @click="extend">{{ $t("orders.extend") }}</Button>
            </div>
        </div>
        <div v-else class="bg-red-100 p-3 text-center text-sm border-round-lg" :class="class">
            {{ $t("orders.buy_package_info") }}
            <div class="mt-2 mb-2">
                <Button severity="danger" size="small" class="mr-1" @click="order">{{ $t("orders.buy_package") }}</Button>
            </div>
        </div>
    </template>
</template>