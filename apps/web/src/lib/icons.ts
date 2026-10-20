export type IconName =
    | 'home'
    | 'login'
    | 'users'
    | 'clipboard'
    | 'upload'
    | 'printer'
    | 'building'
    | 'shield'
    | 'menu'
    | 'logout'
    | 'pulse'
    | 'arrow'
    | 'folder'
    | 'file'
    | 'database'
    | 'dot';

export const iconPaths: Record<IconName, string> = {
    home: 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5',
    login: 'M10 17l5-5-5-5M15 12H3M15 4h5v16h-5',
    users: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
    clipboard:
        'M9 5H7a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 12h6M9 16h6',
    upload: 'M12 16V4m0 0 4 4m-4-4L8 8M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3',
    printer:
        'M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v7H7z',
    building:
        'M3 21h18M5 21V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v17M14 9h4a1 1 0 0 1 1 1v11M8 7h3M8 11h3M8 15h3',
    shield: 'M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6l-8-3Z',
    menu: 'M4 6h16M4 12h16M4 18h16',
    logout: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
    pulse: 'M3 12h4l2-7 4 14 2-7h6',
    arrow: 'M5 12h14m0 0-6-6m6 6-6 6',
    folder: 'M4 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z',
    file: 'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Zm0 0v5h5M9 13h6M9 17h6',
    database:
        'M12 3c4.4 0 8 1.3 8 3s-3.6 3-8 3-8-1.3-8-3 3.6-3 8-3ZM4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6',
    dot: 'M12 12h.01',
};
