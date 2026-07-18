import  { useState, useEffect } from "react";

const WEDDING_DATE = new Date("2026-09-19T13:30:00"); //}dodati datum iz back-a u istom formatu//

interface TimeLeft {
  days: number;
  hours: number;
  minutes: number;
  seconds: number;
}

export default function CountdownSection() {
  const [timeLeft, setTimeLeft] = useState<TimeLeft>(getTimeLeft());

  function getTimeLeft(): TimeLeft {
    const now = new Date();
    const diff = WEDDING_DATE.getTime() - now.getTime();
    
    if (diff <= 0) return { days: 0, hours: 0, minutes: 0, seconds: 0 };
    
    return {
      days: Math.floor(diff / (1000 * 60 * 60 * 24)),
      hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
      minutes: Math.floor((diff / (1000 * 60)) % 60),
      seconds: Math.floor((diff / 1000) % 60),
    };
  }

  useEffect(() => {
    const interval = setInterval(() => setTimeLeft(getTimeLeft()), 1000);
    return () => clearInterval(interval);
  }, []);

  const units = [
    { value: timeLeft.days, label: "dana" },
    { value: timeLeft.hours, label: "sati" },
    { value: timeLeft.minutes, label: "minuta" },
    { value: timeLeft.seconds, label: "sekundi" },
  ];

  return (
    <div className="px-6 py-12 flex justify-center w-full bg-[#EEF1F5] relative z-20">
      <div className="flex items-start justify-center gap-2 sm:gap-4">
        {units.map((unit, i) => (
          <div key={i} className="relative flex flex-col items-center">
            <div className="relative flex items-center justify-center w-20 h-20 sm:w-20 sm:h-20">
              <svg
                className="w-full h-full"
                viewBox="0 0 24 22"
                fill="none"
                stroke="#9875A6"
                strokeWidth="0.40"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
              </svg>
              <span
                className="absolute font-light italic"
                style={{
                  color: "#0B2F5B",
                  fontFamily: "'Playfair Display', serif",
                  fontSize: "2.25rem",
                  transform: "translateY(-3px)",
                }}
              >
                {String(unit.value).padStart(2, "0")}
              </span>
            </div>
            <span
              className="mt-2 text-[10px] tracking-widest uppercase"
              style={{ color: "#9875A6" }}
            >
              {unit.label}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}