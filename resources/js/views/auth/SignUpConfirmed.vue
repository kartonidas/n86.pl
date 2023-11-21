<script>
    import { ref } from 'vue'
</script>

<template>
    <div class="w-full py-6 px-5 sm:px-8 text-center">
        {{ $t('app.registering_confirmed') }}
        
        <div class="mt-4">
            <router-link :to="{name: 'signin'}">
                {{ $t('app.login') }}
            </router-link>
        </div>
    </div>
</template>