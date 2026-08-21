<style>
    .ios-select-multiple {
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        max-width: 400px;
        margin: 20px auto;
        position: relative;
    }

    .ios-select-multiple .select-trigger {
      padding: 12px 16px;
      background: #fff;
      border: 1px solid #e1e1e1;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .ios-select-multiple .select-trigger:hover {
      background: #f8f8f8;
    }

    .ios-select-multiple .options-container {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        max-height: 400px;
        overflow-y: auto;
        background: #f2f2f7;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        margin-top: 8px;
        z-index: 1000;
        box-sizing: border-box;
    }

    .ios-select-multiple .options-container.adjusted-right {
        left: auto;
        right: 0;
    }

    .ios-select-multiple .search-container {
      position: sticky;
      top: 0;
      padding: 8px;
      background: #f2f2f7;
      z-index: 2;
    }

    .ios-select-multiple .search-input {
      width: 100%;
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      background: #fff;
      font-size: 15px;
      outline: none;
      -webkit-appearance: none;
    }

    .ios-select-multiple .search-input:focus {
      box-shadow: 0 0 0 2px #007AFF;
    }

    /* Hint ringan untuk menjelaskan shortcut double click di dalam dropdown. */
    .ios-select-multiple .multiple-select-hint {
      margin: 0 8px 8px;
      padding: 7px 10px;
      border-radius: 8px;
      background: #fff;
      color: #6b7280;
      font-size: 12px;
      line-height: 1.35;
      border: 1px solid #e5e7eb;
    }
    .ios-select-multiple .option-group {
      margin: 8px 0;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
    }

    .ios-select-multiple .group-header {
      padding: 12px 16px;
      font-weight: 600;
      color: #1c1c1e;
      background: #f8f8f8;
      border-bottom: 1px solid #e1e1e1;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: background-color 0.2s;
    }

    .ios-select-multiple .group-header:hover {
      background: #f0f0f0;
    }

    .ios-select-multiple .group-header .toggle-icon {
      transition: transform 0.2s ease;
      font-size: 16px;
      color: #666;
      font-weight: normal;
    }

    .ios-select-multiple .group-header.collapsed .toggle-icon {
      transform: rotate(-90deg);
    }

    /* Remove the old conflicting rules */

    .ios-select-multiple .group-description {
      padding: 8px 16px;
      font-size: 13px;
      color: #666;
      background: #f2f2f7;
    }

    /* Hierarchical nesting styles */
    .ios-select-multiple .option-group.level-1 {
      margin-left: 0;
    }

    .ios-select-multiple .option-group.level-2,
    .ios-select-multiple .option-group.level-3,
    .ios-select-multiple .option-group.level-4 {
      display: none; /* Hide child groups by default */
    }

    .ios-select-multiple .option-group.level-2 {
      margin-left: 16px;
      border-left: 2px solid #e1e1e1;
      border-bottom: 2px solid #e1e1e1;
    }

    .ios-select-multiple .option-group.level-3 {
      margin-left: 32px;
      border-left: 2px solid #d1d1d1;
      border-bottom: 2px solid #d1d1d1;
    }

    .ios-select-multiple .option-group.level-4 {
      margin-left: 48px;
      border-left: 2px solid #c1c1c1;
    }

    /* Show child groups when parent is expanded */
    .ios-select-multiple .group-header:not(.collapsed) + .option-group,
    .ios-select-multiple .group-header:not(.collapsed) ~ .option-group {
      display: block;
    }

    /* Hide direct child options when group is collapsed */
    .ios-select-multiple .group-header.collapsed ~ .option {
      display: none;
    }

    .ios-select-multiple .group-header.level-1 {
      background: #f8f8f8;
      font-weight: 700;
    }

    .ios-select-multiple .group-header.level-2 {
      background: #fafafa;
      font-weight: 600;
      font-size: 14px;
    }

    .ios-select-multiple .group-header.level-3 {
      background: #fcfcfc;
      font-weight: 500;
      font-size: 13px;
    }

    .ios-select-multiple .group-header.level-4 {
      background: #fdfdfd;
      font-weight: 400;
      font-size: 12px;
    }

    /* Selectable Header Styles */
    .ios-select-multiple .group-header.selectable-header {
      cursor: pointer;
      transition: background-color 0.2s;
      position: relative;
    }

    .ios-select-multiple .group-header.selectable-header:hover {
      background: #f0f0f0;
    }

    .ios-select-multiple .group-header.selectable-header.selected {
      background: #e3f2fd;
      color: #1976d2;
      font-weight: 600;
    }

    .ios-select-multiple .group-header.selectable-header .checkmark {
      margin-right: 8px;
      color: #007AFF;
      opacity: 0;
      font-size: 14px;
      transition: opacity 0.2s;
    }

    .ios-select-multiple .group-header.selectable-header.selected .checkmark {
      opacity: 1;
    }

    .ios-select-multiple .group-header.selectable-header .toggle-icon {
      margin-left: auto;
      cursor: pointer;
      font-size: 16px;
    }

    .ios-select-multiple .group-header.selectable-header .toggle-icon:hover {
      color: #333;
    }

    .ios-select-multiple .option {
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background-color 0.2s;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        position: relative;
    }

    .ios-select-multiple .option.level-2 {
      padding-left: 32px;
      background: #fafafa;
      font-size: 14px;
    }

    .ios-select-multiple .option.level-3 {
      padding-left: 48px;
      background: #fcfcfc;
      font-size: 13px;
    }

    .ios-select-multiple .option.level-4 {
      padding-left: 64px;
      background: #fdfdfd;
      font-size: 12px;
    }

    .ios-select-multiple .option.level-2:hover {
      background: #f0f0f0;
    }

    .ios-select-multiple .option.level-3:hover {
      background: #f5f5f5;
    }

    .ios-select-multiple .option.level-4:hover {
      background: #f8f8f8;
    }

    /* Selected states for different levels */
    .ios-select-multiple .option.level-2.selected {
      background: #e3f2fd;
    }

    .ios-select-multiple .option.level-3.selected {
      background: #e8f5e8;
    }

    .ios-select-multiple .option.level-4.selected {
      background: #fff3e0;
    }

    .ios-select-multiple .option:last-child {
      border-bottom: none;
    }

    .ios-select-multiple .option:hover {
      background-color: #f5f5f5;
    }

    .ios-select-multiple .option.selected {
      background-color: #f0f0f0;
    }

    .ios-select-multiple .option.hidden {
      display: none;
    }

    .ios-select-multiple .checkmark {
      margin-right: 10px;
      color: #007AFF;
      opacity: 0;
    }

    .ios-select-multiple .option.selected .checkmark {
      opacity: 1;
    }

    .ios-select-multiple .selected-text {
        color: #333;
        width: 100%; /* the element needs a fixed width (in px, em, %, etc) */
        overflow: hidden; /* make sure it hides the content that overflows */
        white-space: nowrap; /* don't break the line */
        text-overflow: ellipsis; /* give the beautiful '...' effect */
    }

    .ios-select-multiple .arrow {
      border-style: solid;
      border-width: 2px 2px 0 0;
      content: '';
      display: inline-block;
      height: 8px;
      width: 8px;
      position: relative;
      transform: rotate(135deg);
      vertical-align: middle;
    }

    .ios-select-multiple .no-results {
      padding: 16px;
      text-align: center;
      color: #666;
      background: #fff;
      border-radius: 10px;
      margin: 16px 8px;
      display: none;
    }

    /* Responsive rules for smaller devices */
    @media (max-width: 768px) {
      .ios-select-multiple {
        max-width: 100%;
        margin: 10px 0;
      }

      .ios-select-multiple .options-container {
        max-height: 300px;
        left: 0;
        right: 0;
      }

      .ios-select-multiple .option-group.level-2 {
        margin-left: 8px;
      }

      .ios-select-multiple .option-group.level-3 {
        margin-left: 16px;
      }

      .ios-select-multiple .option-group.level-4 {
        margin-left: 24px;
      }

      .ios-select-multiple .option.level-2 {
        padding-left: 24px;
      }

      .ios-select-multiple .option.level-3 {
        padding-left: 32px;
      }

      .ios-select-multiple .option.level-4 {
        padding-left: 40px;
      }

      .ios-select-multiple .group-header,
      .ios-select-multiple .option {
        padding: 10px 12px;
      }

      .ios-select-multiple .search-input {
        padding: 10px;
        font-size: 16px; /* Prevent zoom on iOS */
      }
    }

    @media (max-width: 480px) {
      .ios-select-multiple .options-container {
        max-height: 250px;
      }

      .ios-select-multiple .group-header,
      .ios-select-multiple .option {
        padding: 8px 10px;
        font-size: 14px;
      }

      .ios-select-multiple .option.level-2 {
        padding-left: 20px;
      }

      .ios-select-multiple .option.level-3 {
        padding-left: 28px;
      }

      .ios-select-multiple .option.level-4 {
        padding-left: 36px;
      }
    }

  </style>
