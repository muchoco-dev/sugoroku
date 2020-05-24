<template>
<div id="sugoroku">
    <table class="bg-white table table-borderless w-100">
        <tr>
            <td v-bind:id="n" v-for="n in col_count">
                <div v-if="n === 1">
                    Start
                    <p>{{ setPiece(n) }}</p>
                </div>
                <div v-else-if="getSpaceName(n)">
                    {{ getSpaceName(n) }}
                    <p>{{ setPiece(n) }}</p>
                </div>
                <div v-else>&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td v-bind:id="board.goal_position">
                Goal
                <p>{{ setPiece(board.goal_position) }}</p>
            </td>
            <td border="0" v-for="n in (col_count-2)" class="bg-light">
                &nbsp;
            </td>
            <td v-bind:id="col_count+1">
                {{ getSpaceName(col_count+1) }}
                <p>{{ setPiece(col_count+1) }}</p>
            </td>
        </tr>
        <tr>
            <td v-for="n in col_count" v-bind:id="board.goal_position - n">
                {{ getSpaceName(board.goal_position - n) }}
                <p>{{ setPiece(board.goal_position - n) }}</p>
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
        col_count: 0,
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
        return '';
    },
    setPiece: function (position) {
        let pieces = {
            1: 'user1'
        };
        return pieces[position];
    }
  }
}
</script>
