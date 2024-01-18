<script>
    import { ref } from 'vue'
    import { setMetaTitle } from '@/utils/helper'
    import Package from './_partials/Package.vue';
    
    export default {
        components: { Package },
        setup() {
            setMetaTitle('meta.title.dashboard')
            
            return { }
        },
        data() {
            return {
            }
        },
    }
</script>

<template>
    <div class="grid">
        <div class="col-12 sm:col-4 xl:col-4">
            ILOŚC NIERUCHOMOŚCI:
            10
        </div>
        <div class="col-12 sm:col-4 xl:col-4">
            ILOŚC AKTYWNYCH NAJMÓW:
            10
        </div>
            
        <div class="col-12 sm:col-4 xl:col-4">
            <Package />
        </div>
    </div>
    
    <ul class="list-unstyled">
        <li>Nieopłacone rachunku (o najbliższym tereminie płatnośco)</li>
        <li>Kończące się wynajmy</li>
        <li>Jakiś wykres ilośc nieruchomości per ilość wynajmów na miesiąc</li>
        <li>Może wykres zarobków</li>
    </ul>
    
</template>