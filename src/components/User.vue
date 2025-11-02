<template>
  <div class="user">
      <div class="picture_wrapper flex justify-center"> 
        <img :src="user.avatar" class="" />
      </div>

      <div class="content p-4 flex column ">
        <h2 class="align-center">{{ user.name }}</h2>

        <blockquote v-if="user.intro" class="mt-2 text-gray-600">{{ user.intro }}</blockquote>

        <div v-if="user.periods && user.periods.length" class="text-gray-600 align-start">
          <h4 class="mb-1">Stationen</h4>
          <ul>
            <li v-for="(p, i) in activities" :key="i">
              <strong>{{ formatDate(p.from) }} – {{ formatDate(p.to) || 'heute' }}</strong> <br/>
              <span v-if="p.activity">{{ p.activity }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import IUser from '@/src/interfaces/IUser.ts';
import { computed } from 'vue'

const props = defineProps({
  user:{
    type: Object as IUser,
    required: true
  }
})

const activities = computed(() => {
  return props.user.periods?.sort((a, b) => {
    const dateA = a.to ? new Date(a.to).getTime() : Date.now();
    const dateB = b.to ? new Date(b.to).getTime() : Date.now();
    return dateB - dateA;
  });
})

function formatDate(date: string | number | Date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString("de-DE", { year: 'numeric', month: 'long' })
}
</script>

<style scoped lang="scss">
.user {
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    width: max-content;
    box-shadow: black 5px 5px 10px 0px;
    max-width: 22%;
    min-width: 200px;
    overflow: hidden; 
    margin: 20px 7.5px;

    .picture_wrapper {
        background-color: #00008b4a;
        margin-bottom: 1.5em;

        img {
            width: 100%;
            max-height: 350px;
            margin-bottom: 0;
        }
    }

    .content {
        position: relative;
    }

    blockquote {
      font-size: x-large;
      width: -webkit-fill-available;
      word-break: break-word;
      text-align: center;
      position: relative;
      padding: 45px 35px;
      margin: 0;
    }

    blockquote:before {
        content: "\201C";
        font-size: 110px;
        line-height: 0.96;
        top: 0;
        left: 0;
        font-family: "Lora";
        font-style: italic;
        position: absolute;
    }
    blockquote:after {
        content: "\201D";
        font-size: 110px;
        line-height: 0.96;
        bottom: -55px;
        right: 0;
        font-family: "Lora";
        font-style: italic;
        position: absolute;
    }
}


</style>