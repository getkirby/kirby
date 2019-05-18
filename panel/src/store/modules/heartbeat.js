import Vue from "vue";

export default {
  namespaced: true,
  state: {
    instance: null,
    clock: 0,
    step: 5,
    beats: []
  },
  mutations: {
    ADD(state, beat) {
      state.beats.push(beat);
    },
    CLEAR(state) {
      clearInterval(state.instance);
      state.clock = 0;
    },
    CLOCK(state) {
      state.clock += state.step;
    },
    INITIALIZE(state, interval) {
      state.instance = interval;
    },
    REMOVE(state, handler) {
      const index = state.beats.map(b => b.handler).indexOf(handler);
      if (index !== -1) {
        Vue.delete(state.beats, index);
      }
    }
  },
  actions: {
    add(context, beat) {
      beat = {
        handler: beat[0] || beat,
        interval: beat[1] || context.state.step
      };

      beat.handler();
      context.commit("ADD", beat);

      if (context.state.beats.length === 1) {
        context.dispatch("run");
      }
    },
    remove(context, beat) {
      context.commit("REMOVE", beat);

      if (context.state.beats.length < 1) {
        context.commit("CLEAR");
      }
    },
    run(context) {
      context.commit("CLEAR");
      context.commit("INITIALIZE", setInterval(() => {
        context.commit("CLOCK");
        context.state.beats.forEach(beat => {
          if (context.state.clock % beat.interval === 0) {
            beat.handler();
          }
        });
      }, context.state.step * 1000));
    }
  }
};
