<style>
    .target-element {
        cursor: pointer;
        user-select: none;
    }

    .context-menu {
        position: fixed;
        opacity: 0;
        transform: scale(0.96);
        transform-origin: top center;
        transition: all 0.25s cubic-bezier(0.3, 0.15, 0.3, 1);
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        border-radius: 14px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.12),
                    0 2px 12px rgba(0,0,0,0.08);
        min-width: 250px;
        z-index: 1000;
        pointer-events: none;
        padding: 6px;
    }

    .context-menu.visible {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
    }

    .context-menu-item {
        padding: 10px;
        margin: 2px 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #1c1c1e;
        font-size: 17px;
        border-radius: 10px;
        font-weight: 400;
        transition: background-color 0.15s;
    }

        .context-menu-item:active {
            background: rgba(0,0,0,0.08);
            transition: none;
        }

        @media (hover: hover) {
            .context-menu-item:hover {
                background: rgba(0,0,0,0.05);
            }
        }

        .context-menu-item.destructive {
            color: #ff3b30;
        }

        .divider {
            height: 1px;
            background: rgba(60,60,67,0.1);
            margin: 6px 0;
        }

        .menu-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 14px;
        }
        .context-menu-section {
            margin: 4px 0;
        }
</style>
