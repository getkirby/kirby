export default {
  youtube(url) {

    if (!url.match("youtu")) {
      return false;
    }

    let uri = null;

    try {
      uri = new URL(url);
    } catch (e) {
      return false;
    }

    const path = uri.pathname.split("/").filter(item => item !== "");
    const first = path[0];
    const second = path[1];
    const host = "https://" + uri.host + "/embed";

    const isYoutubeId = (id) => {
      return id.match(/^[a-zA-Z0-9_-]+$/) !== null;
    };

    let query = uri.searchParams;

    // build the correct base URL for the embed,
    // the query params are appended below
    let src = null;
    switch (path.join("/")) {
      case "embed/videos":
      case "playlist":
        if (isYoutubeId(query.get("list"))) {
          src = host + "/videoseries";
        }
        break;
      case "watch":
        if (isYoutubeId(query.get("v"))) {
          src = host + "/" + query.get("v");

          if (query.has("t")) {
            query.set("start", query.get("t"));
          }

          query.delete("v");
          query.delete("t");
        }

        break;
      default:
        // short URLs
        if (uri.host.includes("youtu.be")) {
          src = 'https://www.youtube.com/embed/' + first;

          if (query.has("t")) {
            query.set("start", query.get("t"));
          }

          query.delete("t");
        } else if (first === "embed" && isYoutubeId(second)) {
          src = host + "/" + second;
        }
    }

    if (!src) {
      return false;
    }

    const queryString = query.toString();

    if (queryString.length) {
      src += "?" + queryString.replace("&", "&amp;");
    }

    return src;

  },
  video(url) {

    // YouTube video
    if (url.includes("youtu")) {
      return this.youtube(url);
    }

    // Vimeo video
    if (url.includes("vimeo")) {
      return this.vimeo(url);
    }

    return false;

  },
  vimeo(url) {

    let uri = null;

    try {
      uri = new URL(url);
    } catch (e) {
      return false;
    }

    const path = uri.pathname.split("/").filter(item => item !== "");

    let query = uri.searchParams;
    let id = null;

    switch (uri.host) {
      case "vimeo.com":
      case "www.vimeo.com":
        id = path[0];
        break;
      case "player.vimeo.com":
        id = path[1];
        break;
    }

    if (!id.match(/^[0-9]*$/)) {
      return false;
    }

    let src = "https://player.vimeo.com/video/" + id;

    const queryString = query.toString();

    if (queryString.length) {
      src += "?" + queryString.replace("&", "&amp;");
    }

    return src;

  }
};
