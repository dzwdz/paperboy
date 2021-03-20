#!/usr/bin/env ruby

require 'rss'
require 'open-uri'

urls = [
  "https://xkcd.com/rss.xml",
  "https://esoteric.codes/rss",
  "https://jvns.ca/atom.xml"
]

urls.map do |url|
  Thread.new do
    URI.open(url) do

      |rss|
      feed = RSS::Parser.parse rss
      case feed.feed_type
      when 'rss'
        tags = "rss\t#{feed.channel.title}"
        feed.items.each do
          |item|
          puts "#{item.link}\t#{item.title}\t#{item.pubDate.to_i}\t#{tags}"
        end
      when 'atom'
        tags = "rss\t#{feed.title.content}"
        feed.items.each do
          |item|
          puts "#{item.link.href}\t#{item.title.content}\t#{item.updated.content.to_i}\t#{tags}"
        end
      end

    end
  end
end.map(&:join)
