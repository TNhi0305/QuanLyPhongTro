import { isRunningInExpoGo } from 'expo';
import * as Device from 'expo-device';
import { Platform } from 'react-native';

type NotificationsModule = typeof import('expo-notifications');
type Notification = import('expo-notifications').Notification;
type NotificationResponse = import('expo-notifications').NotificationResponse;

const isUnsupportedExpoGo = Platform.OS === 'android' && isRunningInExpoGo();
let expoGoWarningShown = false;
let notificationHandlerConfigured = false;

function warnIfExpoGoUnsupported(): void {
  if (isUnsupportedExpoGo && !expoGoWarningShown) {
    console.warn(
      '[Notifications] Android Expo Go lacks the required native module. Use a development build to enable notifications.'
    );
    expoGoWarningShown = true;
  }
}

async function loadNotifications(): Promise<NotificationsModule | null> {
  if (Platform.OS === 'web' || isUnsupportedExpoGo) {
    warnIfExpoGoUnsupported();
    return null;
  }

  let notifications: NotificationsModule;
  try {
    notifications = await import('expo-notifications');
  } catch (error) {
    console.warn('[Notifications] Failed to load the native notification module:', error);
    return null;
  }
  if (!notificationHandlerConfigured) {
    notifications.setNotificationHandler({
      handleNotification: async () => ({
        shouldShowAlert: true,
        shouldPlaySound: true,
        shouldSetBadge: true,
        shouldShowBanner: true,
        shouldShowList: true,
        priority: notifications.AndroidNotificationPriority?.MAX ?? 3
      })
    });
    notificationHandlerConfigured = true;
  }
  return notifications;
}

export async function subscribeToNotificationEvents(
  onReceived: (notification: Notification) => void,
  onResponse: (response: NotificationResponse) => void
): Promise<() => void> {
  const notifications = await loadNotifications();
  if (!notifications) return () => {};

  const receivedSubscription = notifications.addNotificationReceivedListener(onReceived);
  const responseSubscription = notifications.addNotificationResponseReceivedListener(onResponse);
  return () => {
    receivedSubscription.remove();
    responseSubscription.remove();
  };
}

export interface NotificationPayload {
  notification_id?: number | string;
  type?: string;
  reference_type?: string;
  reference_id?: number | string;
  invoice_id?: number | string;
  request_id?: number | string;
  [key: string]: any;
}

export function requiresNotificationDevelopmentBuild(): boolean {
  return isUnsupportedExpoGo;
}

/**
 * Register device for push notifications and get Expo Push Token.
 */
export async function registerForPushNotificationsAsync(): Promise<string | null> {
  const Notifications = await loadNotifications();
  if (!Notifications) {
    return null;
  }

  try {
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Thông báo Quản lý phòng trọ',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#208AEF',
        sound: 'default',
        enableLights: true,
        enableVibrate: true
      });
    }

    if (!Device.isDevice) {
      console.log('[Push] Must use physical device for remote push notifications.');
      // Return a simulated token for simulator demo purposes
      return `ExponentPushToken[SimDevice_${Platform.OS}_${Date.now()}]`;
    }

    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      console.log('[Push] Permission not granted by user.');
      return null;
    }

    const tokenResponse = await Notifications.getExpoPushTokenAsync({
      // projectId is optional in Expo Go, inferred from project configuration
    });

    const token = tokenResponse.data;
    console.log('[Push] Registered Expo Push Token:', token);
    return token;
  } catch (error) {
    console.warn('[Push] Error getting push token:', error);
    return null;
  }
}

/**
 * Schedule a local notification (e.g. Bill payment reminder).
 */
export async function scheduleLocalReminder(params: {
  title: string;
  body: string;
  seconds?: number;
  data?: NotificationPayload;
}): Promise<string | null> {
  const Notifications = await loadNotifications();
  if (!Notifications) return null;

  try {
    // 1. Kiểm tra và xin quyền thông báo nếu chưa có
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;
    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }
    if (finalStatus !== 'granted') {
      console.warn('[LocalNotification] Quyền thông báo chưa được cấp bởi người dùng.');
      return null;
    }

    // 2. Đảm bảo Notification Channel tồn tại trên Android với độ ưu tiên MAX
    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Thông báo Quản lý phòng trọ',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#208AEF',
        sound: 'default',
        enableLights: true,
        enableVibrate: true,
        showBadge: true
      });
    }

    // 3. Cấu hình trigger: nếu seconds > 0 thì lên lịch, còn 0 thì bắn ngay lập tức
    const delaySeconds = typeof params.seconds === 'number' ? params.seconds : 1;
    const trigger = delaySeconds > 0
      ? {
          type: Notifications.SchedulableTriggerInputTypes.TIME_INTERVAL,
          seconds: delaySeconds,
          repeats: false,
          channelId: 'default'
        }
      : {
          channelId: 'default'
        };

    const id = await Notifications.scheduleNotificationAsync({
      content: {
        title: params.title,
        body: params.body,
        data: params.data || {},
        sound: 'default',
        priority: Notifications.AndroidNotificationPriority?.MAX ?? 3
      },
      trigger: trigger as any
    });
    console.log(`[LocalNotification] Đã lên lịch #${id} sau ${delaySeconds}s`);
    return id;
  } catch (err) {
    console.warn('[LocalNotification] Lỗi lên lịch thông báo:', err);
    return null;
  }
}

/**
 * Cancel a scheduled local notification.
 */
export async function cancelScheduledReminder(id: string): Promise<void> {
  const Notifications = await loadNotifications();
  if (!Notifications) return;

  try {
    await Notifications.cancelScheduledNotificationAsync(id);
  } catch (e) {
    console.warn('Error cancelling notification:', e);
  }
}

/**
 * Check if the app was launched by tapping a notification (cold start / terminated state).
 */
export async function checkInitialNotification(): Promise<NotificationPayload | null> {
  const Notifications = await loadNotifications();
  if (!Notifications) return null;

  try {
    const response = await Notifications.getLastNotificationResponseAsync();
    if (response) {
      const data = response.notification.request.content.data as NotificationPayload;
      console.log('[Push] App opened from cold start notification:', data);
      return data;
    }
  } catch (err) {
    console.warn('[Push] Error checking initial notification:', err);
  }
  return null;
}
