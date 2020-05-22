<template>
<div id="logs">
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
    this.logs.push('test');

    window.Echo.private('sugoroku-started-channel.' + this.room_id)
      .listen('SugorokuStarted',response => {
        this.logs.push('ゲームスタート！');
      });
  }
}
</script>
