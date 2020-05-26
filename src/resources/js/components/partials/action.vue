<template>
<div id="action">
    <button class="btn btn-success" v-if="canShowStartButton()" @click="start()">ゲームスタート</button>
    <div class="input-group mt-4" v-if="!is_started">
        <div class="input-group-prepend">
            <span class="input-group-text">招待URL</span>
        </div>
        <input class="copy form-control bg-white" type="text" v-model="join_url" :data-clipboard-text="join_url" readonly>
    </div>
    <button @click="delete">削除</button>
</div>
</template>
<script>
export default {
  props: {
    room: Object,
    user_id: Number,
    room_status_open: Number,
    token: String
  },
  data: function () {
    return {
      is_started: false,
      join_url: location.href + '/join'
    }
  },
  methods: {
    canShowStartButton: function () {
      if (this.room.owner_id === this.user_id &&
            this.room.status === this.room_status_open) {
        if (!this.is_started) {
          return true;
        }
      }
      return false;
    },
    start: function () {
      axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
      axios.post(
        '/api/sugoroku/start',
        {
          headers: {
            "Content-Type": "application/json"
          },
          'room_id': this.room.id
        }).then(response => {
          if (response.data.status === 'success') {
            console.log('ゲームをスタートしました');
            this.is_started = true;
          } else {
            alert(response.data.message);
          }
        }).catch(function(error) {
          console.log(error);
        });

    },
    // 部屋の削除
    delete: function ()  {
      balus(Auth::id())
    },
    getMember: function () {
      console.log('aaaaaa');
        axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
        axios.get(
         '/api/get_member' + this.room.id + '/' + this.user_id,
          {
            headers: {
              "Content-Type": "application/json"
          },
      }).then(function(response) {
          console.log(response.data)
      }).catch(function(error) {
          console.log(error);
      });
    }
  }
}
</script>
