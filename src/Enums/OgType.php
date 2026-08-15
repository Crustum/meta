<?php
declare(strict_types=1);

namespace Crustum\Meta\Enums;

/**
 * Open Graph object type values.
 */
enum OgType: string
{
    case Website = 'website';
    case Article = 'article';
    case Book = 'book';
    case Profile = 'profile';
    case MusicSong = 'music.song';
    case MusicAlbum = 'music.album';
    case MusicPlaylist = 'music.playlist';
    case MusicRadioStation = 'music.radio_station';
    case VideoMovie = 'video.movie';
    case VideoEpisode = 'video.episode';
    case VideoTvShow = 'video.tv_show';
    case VideoOther = 'video.other';
}
