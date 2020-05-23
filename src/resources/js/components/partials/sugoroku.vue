<template>
<div id="sugoroku">
    <table class="bg-white table table-borderless w-100">
        <tr>
            <td v-bind:id="n" v-for="n in col_count">
                <div v-if="n === 1">Start</div>
                <div v-else-if="getSpaceName(n)">{{ getSpaceName(n) }}</div>
                <div v-else>&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td v-bind:id="board.goal_position">Goal</td>
            <td border="0" v-for="n in (col_count-2)" class="bg-light">
                &nbsp;
            </td>
            <td v-bind:id="col_count+1">&nbsp;</td>
        </tr>
        <tr>
            <td v-for="n in col_count" v-bind:id="board.goal_position - n">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
</template>
<script>
export default {
  props: {
      board: Object,
      spaces: Object
  },
  data() {
    return {
        col_count: 0
    }
  },
  created: function () {
      this.col_count = (Number(this.board.goal_position) - 2) / 2;
  },
  methods: {
    getSpaceName: function (id) {
        if (this.spaces[id]) {
            return this.spaces[id].name;
        }
        return false;
    },
    setPiece: function () {
        
    }
  }
}
</script>
