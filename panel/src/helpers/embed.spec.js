import embed from "./embed.js";

describe("$helper.embed()", () => {

  it("should create the right embed URLs", () => {

    const tests = [
      // YouTube
      [
        'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'http://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'http://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'http://www.youtube.com/embed/d9NF2edxy-M',
        'https://www.youtube.com/embed/d9NF2edxy-M'
      ],
      [
        'http://www.youtube.com/embed/d9NF2edxy-M?start=10',
        'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'http://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M',
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
      ],
      [
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10',
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M',
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
      ],
      [
        'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M&t=10',
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'https://www.youtube-nocookie.com/watch?test=value&v=d9NF2edxy-M&t=10',
        'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&start=10'
      ],
      [
        'https://www.youtube-nocookie.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'https://www.youtube-nocookie.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'http://www.youtube.com/watch?v=d9NF2edxy-M',
        'https://www.youtube.com/embed/d9NF2edxy-M'
      ],
      [
        'http://www.youtube.com/watch?test=value&v=d9NF2edxy-M',
        'https://www.youtube.com/embed/d9NF2edxy-M?test=value'
      ],
      [
        'http://www.youtube.com/watch?v=d9NF2edxy-M&t=10',
        'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'https://www.youtube.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'https://www.youtube.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
        'https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
      ],
      [
        'https://www.youtu.be/d9NF2edxy-M',
        'https://www.youtube.com/embed/d9NF2edxy-M'
      ],
      [
        'https://www.youtu.be/d9NF2edxy-M?t=10',
        'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'https://youtu.be/d9NF2edxy-M?t=10',
        'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
      ],
      [
        'https://www.youtu.be/d9NF2edxy-M?test=value&t=10',
        'https://www.youtube.com/embed/d9NF2edxy-M?test=value&start=10'
      ],

      // Vimeo
      [
        'https://vimeo.com/239882943',
        'https://player.vimeo.com/video/239882943'
      ],
      [
        'https://vimeo.com/239882943?test=value',
        'https://player.vimeo.com/video/239882943?test=value'
      ],
      [
        'https://player.vimeo.com/video/239882943',
        'https://player.vimeo.com/video/239882943'
      ],
      [
        'https://player.vimeo.com/video/239882943?test=value',
        'https://player.vimeo.com/video/239882943?test=value'
      ],

      // invalid URLs
      [
        'https://getkirby.com',
        false
      ],
      [
        'https://youtube.com/imprint',
        false
      ],
      [
        'https://youtube.com/watch?v=öööö',
        false
      ],
      [
        'https://vimeo.com/öööö',
        false
      ]
    ];

    tests.forEach(test => {
      const input = test[0];
      const expected = test[1];
      const result = embed.video(input);

      expect(result).to.equal(expected);
    });
  });

});
