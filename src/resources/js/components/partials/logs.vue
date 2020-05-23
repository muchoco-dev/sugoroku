<template>
<div id="logs" class="border h-100">
  <p v-for="log in logs">{{ log }}</p>
</div>
</template>
<script>
export default {
  props: {
    room_id: Number
  },
  data: function () {
    return {
      logs: []
    }
  },
  mounted: function () {
    window.Echo.private('sugoroku-started-channel.' + this.room_id)
      .listen('SugorokuStarted',response => {
        this.logs.push('ゲームスタート！');
      });
  }
}
</script>
