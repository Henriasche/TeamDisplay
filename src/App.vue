<template>
  <div class="grid gap-4">
    <div v-for="u in users" :key="u.id" class="p-4 shadow rounded">
      <div class="flex items-center space-x-4">
        <img :src="u.avatar" class="w-16 h-16 rounded-full" />
        <div>
          <h3 class="font-semibold">{{ u.name }}</h3>
          <p class="text-sm text-gray-600">Status: {{ u.status }}</p>
        </div>
      </div>

      <p class="mt-2">{{ u.intro }}</p>

      <div v-if="u.periods && u.periods.length" class="mt-2 text-sm text-gray-600">
        <h4 class="font-semibold mb-1">Aktivitätszeiträume:</h4>
        <ul>
          <li v-for="(p, i) in u.periods" :key="i">
            <strong>{{ formatDate(p.from) }} – {{ formatDate(p.to) || 'heute' }}</strong><br>
            <span v-if="p.activity">{{ p.activity }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const users = ref([])

onMounted(async () => {
  // Get API URL from WordPress or fallback to current origin
  const apiUrl = window.teamdisplayConfig?.apiUrl || '/wp-json/teamdisplay/v1/intros'
  const res = await fetch(apiUrl)
  users.value = await res.json()
})

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}
</script>