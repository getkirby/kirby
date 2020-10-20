import { undo, redo } from "prosemirror-history";

export default function (props) {

  const trigger = function (event) {
    if (props[event]) {
      props[event]();
    }
  }

  const onEnter = function () {
    trigger("onEnter");
  };

  const onShiftEnter = function () {
    trigger("onShiftEnter");
  };

  const onTab = function () {
    trigger("onTab");
    return true;
  };

  const onShiftTab = function () {
    trigger("onShiftTab");
    return true;
  };

  return {
    "Cmd-z": undo,
    "Cmd-Shift-z": redo,
    "Enter": onEnter,
    "Shift-Enter": onShiftEnter,
    "Shift-Tab": onShiftTab,
    "Tab": onTab
  };

};
