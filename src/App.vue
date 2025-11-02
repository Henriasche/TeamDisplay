<template>
  <div class="user-list">
      <User
        v-for="u in users"
        :key="u.id"
        :user="u"
      />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import User from './components/User.vue'
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

<style scoped lang="scss">
.user-list {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-around;
}
</style>